<?php

namespace DuckyCMS\Setup;

use DuckyCMS\AlertType;
use PDOException;
use function DuckyCMS\DB\get_user_by_username;
use function DuckyCMS\DB\update_user_session_token;
use function DuckyCMS\dcms_alert;
use function DuckyCMS\dcms_get_base_url;
use function DuckyCMS\dcms_require_module;

/**
 * Exit if not accessed directly
 */
if (realpath(__FILE__) !== realpath($_SERVER['SCRIPT_FILENAME'])) {
  exit('Nope.');
}

/**
 * Includes
 */
require_once '../../bootstrap.php';

/*
 * Load required modules using lazy loading
 */
dcms_require_module('db');
dcms_require_module('templates');

session_start();

/**
 * Handle POST login, returns message
 *
 * @return string
 * @throws
 */
function handle_login(): string
{
  $request_method = $_SERVER['REQUEST_METHOD'] ?? '';

  if ($request_method !== 'POST') {
    return '';
  }

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $db_path  = DUCKY_ROOT . '/db/ducky.sqlite';

  $greetings = [
    "Waddle you do Next?"
  ];

  if (!file_exists($db_path)) {
    return '<p>Database not found.</p>';
  }

  try {
    $user = get_user_by_username($username, $db_path);

    if ($user && password_verify($password, $user['password'])) {
      session_regenerate_id(true);
      $token      = bin2hex(random_bytes(32));
      $created_at = time();

      update_user_session_token($user['id'], $token, $created_at, $db_path);

      $_SESSION['username']      = $user['username'];
      $_SESSION['user_id']       = $user['id'];
      $_SESSION['session_token'] = $token;

      header('Location: ' . dcms_get_base_url() . 'admin');
      exit;
    }

    return dcms_alert('Invalid username or password.', AlertType::warning);
  } catch (PDOException $e) {
    $error_message = htmlspecialchars($e->getMessage());
    return dcms_alert('Error: ' . $error_message, AlertType::danger);
  }
}

$message = handle_login();

ob_start();
?>
  <p>Please log in to your account to access the dashboard.</p>
  <form method="post">
    <div>
      <label for="username">Username:</label>
      <input id="username" name="username" type="text" required>
    </div>
    <div>
      <label for="password">Password:</label>
      <input id="password" name="password" type="password" required><br>
    </div>
    <br/>
    <button class="button">Log In</button>
  </form>
  <?php if (!empty($message)) echo $message; ?>
  <?php dcms_render_setup_layout("Login", ob_get_clean()); ?>