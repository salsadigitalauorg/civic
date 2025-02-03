<?php

/**
 * @file
 * CivicTheme libraries processing.
 */

declare(strict_types=1);

use Drupal\civictheme\CivicthemeColorManager;
use Drupal\civictheme\CivicthemeConfigManager;

/**
 * Alter libraries to inject dynamically generated stylesheet.
 *
 * Override current CSS-variables file with a dynamically generated styles
 * or remove the file altogether if the Color Selector is not used in config.
 *
 * This hook runs in a context of the extension that provides the library.
 * For themes, this means that a parent theme may provide a library used by
 * a sub-theme.
 *
 * We are using this hook to implement certain library adjustments on behalf
 * of sub-themes so that they do not have to implement this logic.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
function _civictheme_library_info_alter__color_selector(array &$libraries, string $extension): void {
  // Filter for the current theme or a parent theme (in case if current theme is
  // a sub-theme of a theme that declares libraries).
  $themes = \Drupal::service('theme_handler')->listInfo();
  if (array_key_exists($extension, $themes)) {
    // If there is no declaration of our library - there is nothing for us to do
    // so we exit early.
    if (empty($libraries['css-variables'])) {
      return;
    }

    $active_theme = \Drupal::theme()->getActiveTheme();
    /** @var \Drupal\civictheme\CivicthemeConfigManager $theme_config_manager */
    $theme_config_manager = \Drupal::classResolver(CivicthemeConfigManager::class);
    /** @var \Drupal\civictheme\CivicthemeColorManager $color_manager */
    $color_manager = \Drupal::classResolver(CivicthemeColorManager::class);

    $use_color_selector = $theme_config_manager->load('colors.use_color_selector', FALSE);

    // Only apply if Color Selector was explicitly enabled in configuration.
    if ($use_color_selector) {
      // Set CSS variables stylesheet path to use CSS variables as initial
      // values within a Color Manager instance.
      if (!empty($libraries['css-variables']['css']['theme'])) {
        $css_filepath = key($libraries['css-variables']['css']['theme']);
        // Sub-themes may override paths to CSS variables file via library
        // overrides.
        // Resolve sub-theme overrides as core overrides resolution
        // is executed after this hook.
        $overrides = $active_theme->getLibrariesOverride();
        if (!empty($overrides[$active_theme->getPath()]['civictheme/css-variables']['css']['theme'])) {
          $css_filepath = reset($overrides[$active_theme->getPath()]['civictheme/css-variables']['css']['theme']);
        }

        $color_manager->setCssColorsFilePath($active_theme->getPath() . DIRECTORY_SEPARATOR . $css_filepath);
      }

      // Generate stylesheet suffixed with the current theme name.
      $stylesheet_path = $color_manager->stylesheet($active_theme->getName());

      // Stylesheet generation may fail if, for example, no CSS styles or
      // config provided - only proceed if we have a valid stylesheet path.
      if ($stylesheet_path !== '' && $stylesheet_path !== '0') {
        // Replace existing stylesheet with dynamically generated styles and
        // exclude from pre-processing.
        $libraries['css-variables']['css']['theme'] = [
          $stylesheet_path => [
            'preprocess' => FALSE,
            'weight' => 10,
          ],
        ];
      }
    }
    else {
      // Disable inclusion of the stylesheet.
      foreach (array_keys($libraries['css-variables']['css']['theme']) as $path) {
        unset($libraries['css-variables']['css']['theme'][$path]);
      }
    }
  }
}
