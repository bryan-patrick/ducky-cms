<?php
/**
 * Renders the full HTML layout for a page.
 *
 * @param string $title The page title shown in the title tag and header.
 * @param string $content The HTML content to insert into the main section.
 * @param string|null $current_menu Optional: which menu item is current
 */

namespace DuckyCMS\Setup;

use function DuckyCMS\dcms_get_base_url;
use function DuckyCMS\dcms_render_ducky_logo;
use function DuckyCMS\dcms_render_menu;
use function DuckyCMS\dcms_require_module;

function dcms_render_dashboard_layout(
  string      $title = '',
  string      $content = '',
  string|null $current_menu_item = null,
  string      $sidebar_content = ''): void
{
  dcms_require_module('partials');
  $base_url      = dcms_get_base_url();
  $dashboard_url = $base_url . 'admin/';
  $pages_url     = $base_url . 'admin/pages/';
  $settings_url  = $base_url . 'admin/settings/';
  ?>
  <!DOCTYPE html>
  <html lang="en" style="background-color:#23272d;">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A cozy handcrafted CMS.">
    <meta name="author" content="Ducky">
    <link rel="stylesheet" href=<?= $base_url . "dist/admin.css" ?>>
    <title><?= htmlspecialchars($title) ?></title>
  </head>
  <body>
  <aside>
    <div class="branding">
      <?= dcms_render_ducky_logo(["width" => 35]) ?>
      <span class="site-title">Ducky CMS</span>
    </div>
    <nav role="navigation" aria-label="Main menu">
      <div class="nav-inner">
        <?= dcms_render_menu([
          [
            'name'       => 'Dashboard',
            'width'      => 24,
            'href'       => $dashboard_url,
            'is_current' => ($current_menu_item === 'dashboard'),
          ],
          [
            'name'       => 'Pages',
            'width'      => 24,
            'href'       => $pages_url,
            'is_current' => ($current_menu_item === 'pages'),
          ],
          [
            'name'       => 'Settings',
            'width'      => 24,
            'href'       => $settings_url,
            'is_current' => ($current_menu_item === 'settings'),
          ],
        ])
        ?>
      </div>
    </nav>
  </aside>
  <main>
    <?= $content ?>
  </main>
  <?php if ($sidebar_content): ?>
  <aside class="right-sidebar">
    <?= $sidebar_content ?>
  </aside>
  <?php endif; ?>
  <script>
    (function(){
      var container = document.querySelector('aside nav .nav-inner');
      if (!container) return;
      /**
       * Enhance details for accessibility only (no accordion behavior):
       * - Allow Esc to close the currently focused section.
       */
      var detailsList = container.querySelectorAll('ul > li.has-children > details');
      detailsList.forEach(function(d){
        d.addEventListener('keydown', function(e){
          if (e.key === 'Escape'){
            d.removeAttribute('open');
            var sum = d.querySelector('summary');
            if (sum) sum.focus();
          }
        });
      });
    })();
  </script>
  <script type="module" src="<?= $base_url . 'src/js/app.js' ?>"></script>
  </body>
  </html>
  <?php
}