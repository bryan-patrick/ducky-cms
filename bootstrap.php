<?php
/**
 * Minimal bootstrap for DuckyCMS with lazy loading support.
 * Only defines DUCKY_ROOT and loads core functions.
 * Additional modules are loaded on-demand via dcms_require_module().
 */

if (!defined('DUCKY_ROOT')) {
  define('DUCKY_ROOT', realpath(__DIR__));
}

use function DuckyCMS\dcms_db_exists;
use function DuckyCMS\dcms_get_base_url;

/*
 * Load only core functions initially
 */
require_once DUCKY_ROOT . '/includes/core-functions.php';

/**
 * If we don't have a DB then redirect to setup (unless we're in CLI)
 *
 * Note: PHP_SAPI is a magic const that tells us how the script is being run
 */
if (
  PHP_SAPI !== 'cli' &&
  !dcms_db_exists() &&
  !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/setup/')
) {
  header('Location: ' . dcms_get_base_url() . 'setup/welcome/');
  exit;
}