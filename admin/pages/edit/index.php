<?php
/**
 * Unified page editor - handles both creating new pages and editing existing ones.
 * No id param = create mode. With id param = edit mode.
 * Form posts to itself, redirects to edit view after successful creation.
 */

require_once __DIR__ . '/../../../bootstrap.php';

/*
 * Load required modules using lazy loading
 */
use function DuckyCMS\DB\dcms_create_page;
use function DuckyCMS\DB\get_page_by_id;
use function DuckyCMS\DB\update_page;
use function DuckyCMS\DB\update_page_status;
use function DuckyCMS\dcms_get_base_url;
use function DuckyCMS\dcms_require_login;
use function DuckyCMS\dcms_require_module;
use function DuckyCMS\Setup\dcms_render_dashboard_layout;

dcms_require_module('db');
dcms_require_module('admin');
dcms_require_module('auth');
dcms_require_login();

$page_id = $_GET['id'] ?? null;
$is_create_mode = empty($page_id) || !is_numeric($page_id);
$page = null;

if (!$is_create_mode) {
  $page = get_page_by_id((int)$page_id);

  if (!$page) {
    header('Location: /admin/pages/');
    exit;
  }
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title   = trim($_POST['title'] ?? '');
  $slug    = trim($_POST['slug'] ?? '');
  $content = $_POST['content'] ?? '';

  /**
   * Create mode: the page doesn't exist yet
   */
  if ($is_create_mode) {
    if (!$title || !$slug) {
      $message = '<p>Title and slug are required.</p>';
    }

    if (!$message) {
      $result = dcms_create_page($title, $slug, $content);

      if (is_int($result)) {
        header("Location: " . dcms_get_base_url() . "admin/pages/edit/?id=$result");
        exit();
      }

      $message = "<p>$result</p>";
    }
  }

  /**
   * Edit mode: The page exists, update it
   */
  if (!$is_create_mode) {
    $newStatus = $_POST['status'] ?? $page['status'];
    
    if (!in_array($newStatus, ['draft', 'published'], true)) {
      $newStatus = $page['status'];
    }
    
    $result = update_page($page_id, $title, $slug, $content);

    if ($result === true && $page['status'] !== $newStatus) {
      update_page_status((int)$page_id, $newStatus);
      $page['status'] = $newStatus;
    }

    if ($result === true) {
      $message = '<p>Page updated successfully.</p>';
    }

    if (is_string($result)) {
      $message = "<p>$result</p>";
    }

    if ($result === false) {
      $message = '<p>Failed to update page.</p>';
    }
  }
}

$base_url = dcms_get_base_url();
$pages_url = $base_url . 'admin/pages/';
$trash_url   = $base_url . 'admin/pages/trash/';
$restore_url = $base_url . 'admin/pages/restore/';

if ($is_create_mode) {
  $pages_url .= '?status=draft';
} else {
  $pages_url .= '?status=' . urlencode($page['status']);
}

/**
 * Main content
 */
ob_start(); 

if (!$is_create_mode): 
?>
  <a href="<?= $pages_url ?>">Back to Pages</a>
<?php endif; ?>
<?= $message ?>
  <form method="post" id="edit-page-form">
    <input 
      type="text" 
      id="title" 
      name="title" 
      value="<?= htmlspecialchars($page['title'] ?? '') ?>" 
      placeholder="Page title"
      aria-label="Page title"
      class="title-input"
      required>

    <input 
      type="text" 
      id="slug" 
      name="slug" 
      value="<?= htmlspecialchars($page['slug'] ?? '') ?>" 
      placeholder="Page slug"
      aria-label="Page slug"
      class="slug-input"
      required>

    <label for="content"><?= $is_create_mode ? 'HTML' : 'Content (HTML):' ?></label>
    <textarea id="content" name="content" rows="10"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
  </form>
<?php
$main_content = ob_get_clean();

/**
 * Sidebar
 */
ob_start();
?>
  <button type="submit" form="edit-page-form" class="button"><?= $is_create_mode ? 'Create Page' : 'Update Page' ?></button>

<?php if (!$is_create_mode): ?>
  <label for="status">Status:</label>
  <select id="status" name="status" form="edit-page-form">
    <option value="draft" <?= ($page['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
    <option value="published" <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
  </select>
<?php endif; ?>

<?php if (!$is_create_mode && $page['status'] !== 'trash'): ?>
  <form method="post" action="<?= $trash_url ?>" onsubmit="return confirm('Move to trash?');">
    <input type="hidden" name="id" value="<?= (int)$page['id'] ?>">
    <button type="submit">Move to trash</button>
  </form>
<?php endif; ?>

<?php if (!$is_create_mode && $page['status'] === 'trash'): ?>
  <form method="post" action="<?= $restore_url ?>">
    <input type="hidden" name="id" value="<?= (int)$page['id'] ?>">
    <button type="submit">Restore to draft</button>
  </form>
  <form method="post" action="<?= dcms_get_base_url() . 'admin/pages/delete/' ?>"
        onsubmit="return confirm('Delete forever?');">
    <input type="hidden" name="id" value="<?= (int)$page['id'] ?>">
    <button type="submit" style="color: red;">Delete forever</button>
  </form>
<?php endif;

$sidebar_content = ob_get_clean();
$page_title = $is_create_mode ? 'Create Page' : 'Edit Page';

dcms_render_dashboard_layout($page_title, $main_content, null, $sidebar_content);