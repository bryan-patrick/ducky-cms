<?php
/**
 * Renders the full HTML layout for a page.
 *
 * @param string $title The page title shown in the title tag and header.
 * @param string $content The HTML content to insert into the main section.
 */

namespace DuckyCMS\Setup;

use function DuckyCMS\dcms_get_base_url;
use function DuckyCMS\dcms_require_module;
use function DuckyCMS\dcms_render_ducky_logo;

function dcms_render_setup_layout(string $title = '', string $content = ''): void
{
  dcms_require_module('partials');
  $base_url = dcms_get_base_url();
  ?>
  <!DOCTYPE html>
  <html lang="en" style="background-color:#23272d;">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A cozy handcrafted CMS.">
    <meta name="author" content="Ducky">
    <link rel="stylesheet" href=<?= $base_url . "dist/setup.css" ?>>
    <title><?= htmlspecialchars($title) ?></title>
  </head>
  <body>
  <div class="content">
    <div class="content-inner">
      <header>
        <span class="branding">
          <?= dcms_render_ducky_logo(["width" => 75]) ?>
          <span class="site-title">Ducky CMS</span>
        </span>
      </header>
      <main>
        <h1>
          <?= htmlspecialchars($title) ?>
        </h1>
        <?= $content ?>
      </main>
      <footer>
        <span>&copy; <?= date('Y') ?> Ducky CMS</span>
      </footer>
    </div>
  </div>
  <script type="module" src="<?= $base_url . 'src/js/app.js' ?>"></script>
  </body>
  </html>
  <?php
}