<?php
/**
 * The initial setup page. The first page of the application.
 */

namespace DuckyCMS\Setup;

require_once dirname(__DIR__, 2) . '/bootstrap.php';

/*
 * Load required modules using lazy loading
 */

use function DuckyCMS\dcms_db_exists;
use function DuckyCMS\dcms_get_base_url;
use function DuckyCMS\dcms_require_module;

dcms_require_module('templates');
dcms_require_module('partials');

// Alert helpers

$has_db        = dcms_db_exists();
$base_url      = dcms_get_base_url();
$create_db_url = $base_url . 'setup/create-database/';
$login_url     = $base_url . 'auth/login/';

ob_start();
?>
  <section>
    <?php if ($has_db) : ?>
      <p>
        It looks like you already have a database.
      </p>
      <p>
        If you want to create a new database, delete the current one and return to this page.
      </p>
      <a class="button" href="<?= $login_url ?>">Log in to Ducky CMS</a>
    <?php else: ?>
      <p>This little ducky needs your help getting started. Ready?</p>
      <a class="button" href="<?= $create_db_url; ?>">Let’s Begin 🐣</a>
    <?php endif; ?>
  </section>

  <?php dcms_render_setup_layout('Get Started', ob_get_clean()); ?>