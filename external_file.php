<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'external_file.civix.php';
// phpcs:enable

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

function _external_file_composer_autoload(): void {
  if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $classLoader = require_once __DIR__ . '/vendor/autoload.php';
    if ($classLoader instanceof \Composer\Autoload\ClassLoader) {
      // Re-register class loader to append it. (It's automatically prepended.)
      $classLoader->unregister();
      $classLoader->register();
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function external_file_civicrm_config(&$config): void {
  _external_file_composer_autoload();
  _external_file_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_container().
 */
function external_file_civicrm_container(ContainerBuilder $container): void {
  _external_file_composer_autoload();
  $globResource = new GlobResource(__DIR__ . '/services', '/*.php', FALSE);
  // Container will be rebuilt if a *.php file is added to services
  $container->addResource($globResource);
  foreach ($globResource->getIterator() as $path => $info) {
    // Container will be rebuilt if file changes
    $container->addResource(new FileResource($path));
    require $path;
  }

  if (function_exists('_external_file_test_civicrm_container')) {
    // Allow to use different services in tests.
    _external_file_test_civicrm_container($container);
  }
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function external_file_civicrm_install(): void {
  _external_file_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function external_file_civicrm_enable(): void {
  _external_file_civix_civicrm_enable();
}
