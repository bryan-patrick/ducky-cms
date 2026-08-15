<?php
require_once __DIR__ . '/../../bootstrap.php';

/*
 * Load required modules using lazy loading
 */
use function DuckyCMS\dcms_require_login;
use function DuckyCMS\DB\dcms_count_pages_by_status;
use function DuckyCMS\DB\dcms_get_page_counts_by_status;
use function DuckyCMS\DB\dcms_get_pages_by_status;
use function DuckyCMS\dcms_get_base_url;
use function DuckyCMS\dcms_require_module;
use function DuckyCMS\Setup\dcms_render_dashboard_layout;

dcms_require_module('auth');
dcms_require_login();
dcms_require_module('db');
dcms_require_module('templates');
dcms_require_module('admin');

$base_url = dcms_get_base_url();

/**
 * Quey params!
 */
$allowed_statuses = ['published', 'draft', 'trash'];
$status           = $_GET['status'] ?? 'published';

if (!in_array($status, $allowed_statuses, true)) {
  $status = 'published';
}

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

/**
 * Badge count and data for active tab
 */
$counts          = dcms_get_page_counts_by_status();
$rows            = dcms_get_pages_by_status($status, $perPage, $offset);
$total           = dcms_count_pages_by_status($status);
$totalPages      = max(1, (int)ceil($total / $perPage));
$create_page_url = $base_url . 'admin/pages/edit/';

ob_start();
?>
  <h1>Pages</h1>

  <nav class="tabs" role="tablist">
    <?php
    foreach ($allowed_statuses as $allowed_status):
      $active = $allowed_status === $status;
      ?>
      <a role="tab"
         aria-selected="<?= $active ? 'true' : 'false' ?>"
         aria-controls="page-list"
         href="?status=<?= urlencode($allowed_status) ?>"
         class="tab <?= $active ? 'is-active' : '' ?>">
        <?= ucfirst($allowed_status) ?> <span class="badge"><?= (int)($counts[$allowed_status] ?? 0) ?></span>
      </a>
    <?php endforeach; ?>
    <a class="button create" href="<?= $create_page_url ?>">Create New Page</a>
  </nav>

  <div id="page-list" role="tabpanel" aria-live="polite">
    <?php if (!$rows): ?>
      <p class="muted">No pages in <?= htmlspecialchars($status) ?>.</p>
    <?php else: ?>
      <ul class="page-list" data-status="<?= htmlspecialchars($status) ?>">
        <?php foreach ($rows as $r): ?>
          <li data-id="<?= (int)$r['id'] ?>" class="page-row">
            <div class="meta">
              <strong><?= htmlspecialchars($r['title'] ?: '(untitled)') ?></strong>
              <code>/<?= htmlspecialchars($r['slug'] ?: '') ?></code>
            </div>
            <div class="actions">
              <?php if ($status === 'trash'): ?>
                <form method="post" action="<?= $base_url ?>admin/pages/restore/" class="inline">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button type="submit">Restore</button>
                </form>
                <form method="post" action="<?= $base_url ?>admin/pages/delete" class="inline"
                      onsubmit="return confirm('Delete forever?');">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button type="submit">Delete forever</button>
                </form>
              <?php else: ?>
                <a href="<?= $base_url . 'admin/pages/edit/?id=' . (int)$r['id'] ?>">Edit</a>
                <form method="post" action="<?= $base_url ?>admin/pages/trash/" class="inline"
                      onsubmit="return confirm('Move to trash?');">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button type="submit">Move to trash</button>
                </form>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
      <nav class="pager">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p === $page): ?>
            <strong><?= $p ?></strong>
          <?php else: ?>
            <a href="?status=<?= urlencode($status) ?>&page=<?= $p ?>"><?= $p ?></a>
          <?php endif; ?>

        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
<?php

dcms_render_dashboard_layout('Pages', ob_get_clean(), 'pages');