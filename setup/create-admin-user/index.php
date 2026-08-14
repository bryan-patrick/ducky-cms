<?php
/**
 * This file is step two of the setup. It creates the admin user.
 */

namespace DuckyCMS\Setup;

use DuckyCMS\AlertType;
use Exception;
use PDOException;
use Random\RandomException;
use function DuckyCMS\DB\create_user;
use function DuckyCMS\DB\get_setting;
use function DuckyCMS\DB\set_setting;
use function DuckyCMS\dcms_alert;
use function DuckyCMS\dcms_get_base_url;
use function DuckyCMS\dcms_require_module;

/**
 * Exit if not accessed directly.
 */
if (realpath(__FILE__) !== realpath($_SERVER['SCRIPT_FILENAME'])) {
  exit('Nope.');
}

require_once dirname(__DIR__, 2) . '/bootstrap.php';

/*
 * Load required modules using lazy loading
 */

dcms_require_module('db');
dcms_require_module('templates');
dcms_require_module('partials');

/**
 * Session and one-time setup token validation.
 * If token is in the URL, validate it against settings (hash match, not used, not expired),
 * then bind to session and redirect to the same page without the token.
 */
session_start();

$token_in_url = $_GET['token'] ?? '';

try {
  if ($token_in_url) {
    $stored_hash   = get_setting('setup_token_hash');
    $stored_expiry = (int)(get_setting('setup_token_expiry') ?? 0);
    $stored_used   = (int)(get_setting('setup_token_used') ?? 0);

    $incoming_hash = hash('sha256', $token_in_url);

    if ($stored_hash && hash_equals($stored_hash, $incoming_hash) && $stored_used === 0 && $stored_expiry > time()) {
      /**
       * Valid token: bind to session and redirect without token in URL
       */
      $_SESSION['setup_token_valid'] = true;

      /**
       * Create a new session ID so attackers can't hijack the session
       */
      session_regenerate_id(true);
      $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
      header('Pragma: no-cache');
      header('Location: ' . $redirect_url);
      exit;
    } else {
      $invalid_token_alert = dcms_alert('The setup link is invalid or has expired. Please create the database again to get a new link.', AlertType::danger);
    }
  }
} catch (PDOException) {
  $invalid_token_alert = dcms_alert('Database error while validating setup link.', AlertType::danger);
}

$can_proceed = !empty($_SESSION['setup_token_valid']);

/**
 * Ensure CSRF token exists for the setup form
 */
if ($can_proceed && empty($_SESSION['csrf_token'])) {
  try {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  } catch (Exception) {
    try {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (RandomException) {
      die();
    }
  }
}

/**
 * Handle adding the admin user to the db.
 *
 * @returns array ['message' => string, 'success' => bool]
 */
function dcms_create_admin_user(): array
{
  global $can_proceed;

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return ['message' => '', 'success' => false];
  }

  if (!$can_proceed) {
    return ['message' => dcms_alert('Access denied. Missing or invalid setup token.', AlertType::danger), 'success' => false];
  }

  /**
   * CSRF verification
   */
  $csrf    = $_POST['csrf'] ?? '';
  $csrf_ok = isset($_SESSION['csrf_token']) && is_string($csrf) && hash_equals($_SESSION['csrf_token'], $csrf);

  if (!$csrf_ok) {
    return ['message' => dcms_alert('Security check failed. Please reload the page and try again.', AlertType::danger), 'success' => false];
  }

  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!$username || !$password) {
    return ['message' => dcms_alert('Username and password are both required.', AlertType::danger), 'success' => false];
  }

  if (!preg_match('/^[a-zA-Z0-9_-]{6,32}$/', $username)) {
    return ['message' => dcms_alert('Invalid username format. Use 6-32 characters: letters, numbers, dashes, or underscores only.', AlertType::danger), 'success' => false];
  }

  if (strlen($password) < 12 || strlen($password) > 128) {
    return ['message' => dcms_alert('Password must be between 12 and 128 characters.', AlertType::danger), 'success' => false];
  }

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  try {
    create_user($username, $hashed_password);

    /**
     * Mark token as used and clear token data from settings/session
     */
    set_setting('setup_token_used', '1');
    set_setting('setup_token_hash', '');
    set_setting('setup_token_expiry', '0');
    unset($_SESSION['setup_token_valid']);
    unset($_SESSION['csrf_token']);

    return ['message' => dcms_alert('Database and admin user created successfully!', AlertType::success), 'success' => true];
  } catch (PDOException $e) {
    return ['message' => dcms_alert('Error creating user: ' . $e->getMessage(), AlertType::danger), 'success' => false];
  }
}

$result  = dcms_create_admin_user();
$message = $result['message'];
$success = $result['success'];

ob_start();
?>
  <section>
    <?php if ($success): ?>
      <?= $message ?>
      <div class="top-margin">
        <a href="<?= dcms_get_base_url() ?>auth/login/" class="button">Continue to Login</a>
      </div>
    <?php else: ?>
      <?php if (isset($invalid_token_alert)) echo $invalid_token_alert; ?>
      <?php if ($can_proceed): ?>
        <p>Pick a <strong>username</strong> and a <strong>password</strong>.</p>
        <?php if (!empty($message)) echo $message; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"/>
          <div>
            <label for="username">Username</label>
            <small id="username-help" class="form-text text-muted">Must be between 6 and 32 characters.</small>
            <input id="username" name="username" type="text" placeholder="ducky_admin" autocomplete="off" required
                   aria-describedby="username-help">
          </div>
          <div>
            <label for="password">Password</label>
            <small id="password-help" class="form-text text-muted">Must be between 12 and 128 characters.</small>
            <input id="password" name="password" type="password" placeholder="••••••••••••" autocomplete="off" required
                   aria-describedby="password-help">
          </div>
          <div class="form-input-container">
            <button class="button" type="submit">Create User</button>
          </div>
        </form>
      <?php else: ?>
        <?= dcms_alert('Access to create admin is blocked. The setup token is missing, invalid, expired, or already used.', AlertType::warning) ?>
        <div class="top-margin">
          <a href="<?= dcms_get_base_url() ?>setup/welcome/" class="button outline small">Back to Setup</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </section>
  <?php
dcms_render_setup_layout('Create Admin User', ob_get_clean());