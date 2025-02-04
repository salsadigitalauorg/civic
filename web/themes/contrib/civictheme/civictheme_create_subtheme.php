<?php

/**
 * @file
 * CivicTheme sub-theme scaffolding.
 *
 *  *
 * Environment variables:
 * - SCRIPT_QUIET: Set to '1' to suppress verbose messages.
 * - SCRIPT_RUN_SKIP: Set to '1' to skip running of the script. Useful when
 *   unit-testing or requiring this file from other files.
 *
 * Usage:
 * @code
 * php civictheme_create_subtheme.php new_machine_name "Human name" "Human description"
 *
 * php civictheme_create_subtheme.php new_machine_name "Human name" "Human description" /path/to/theme/new_machine_name
 * @endcode
 *
 * phpcs:disable Drupal.Commenting.InlineComment.SpacingBefore
 * phpcs:disable Drupal.Commenting.InlineComment.SpacingAfter
 * phpcs:disable DrupalPractice.Commenting.CommentEmptyLine.SpacingAfter
 */

declare(strict_types=1);

/**
 * Defines exit codes.
 */
define('EXIT_SUCCESS', 0);
define('EXIT_ERROR', 1);

/**
 * Defines error level to be reported as an error.
 */
define('ERROR_LEVEL', E_USER_WARNING);

/**
 * Main functionality.
 */
function main(array $argv, int $argc): int {
  $default_new_theme_directory = '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'custom';

  if (in_array($argv[1] ?? NULL, ['--help', '-help', '-h', '-?'])) {
    print_command_help($default_new_theme_directory);

    return EXIT_SUCCESS;
  }

  // Show help if not enough or more than required arguments.
  if ($argc < 4 || $argc > 6) {
    print_command_help($default_new_theme_directory);

    return EXIT_ERROR;
  }

  // Optional remove example flag.
  $remove_examples = array_search('--remove-examples', $argv, TRUE);
  if ($remove_examples) {
    unset($argv[$remove_examples]);
    $argv = array_values($argv);
  }

  // Collect and validate values from arguments.
  $new_theme_machine_name = trim((string) $argv[1]);
  validate_theme_machine_name($new_theme_machine_name);
  $new_theme_name = trim((string) $argv[2]);
  $new_theme_description = trim((string) $argv[3]);
  $new_theme_directory = trim($argv[4] ?? $default_new_theme_directory . DIRECTORY_SEPARATOR . $new_theme_machine_name);

  $new_theme_directory = str_starts_with($new_theme_directory, DIRECTORY_SEPARATOR) ? $new_theme_directory : __DIR__ . DIRECTORY_SEPARATOR . $new_theme_directory;
  $new_theme_directory = file_path_canonicalize($new_theme_directory);

  // Prepare theme stub.
  $stub_path = prepare_stub();

  // Process stub.
  process_stub($stub_path, [
    'machine_name' => $new_theme_machine_name,
    'name' => $new_theme_name,
    'description' => $new_theme_description,
    'path' => $new_theme_directory,
    'remove_examples' => $remove_examples,
    'composerjson' => find_composerjson(__DIR__),
    'packagejson' => find_packagejson(__DIR__),
  ]);

  // Copy files from stub to the new theme path.
  file_copy_recursively($stub_path, $new_theme_directory);

  // Remove stub directory.
  file_remove_dir($stub_path);

  // Print footer message.
  print_footer($new_theme_name, $new_theme_machine_name, $new_theme_directory);

  return EXIT_SUCCESS;
}

/**
 * Print help.
 *
 * @param string $default_new_theme_dir
 *   Default new theme directory.
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
function print_command_help($default_new_theme_dir): void {
  $script_name = basename(__FILE__);
  print <<<EOF
CivicTheme Starter Kit scaffolding
----------------------------------

Arguments:
  machine_name           New theme machine name.
  name                   New theme human-readable name.
  description            New theme description.
  new_theme_directory    Optional new theme directory, including theme machine
                         name. Defaults to {$default_new_theme_dir}/machine_name.

Options:
  --help                 This help.
  --remove-examples      Remove example component from generated theme.

Examples:
  php {$script_name} civictheme_demo "CivicTheme Demo" "Demo sub-theme for a CivicTheme theme."

  php {$script_name} civictheme_demo "CivicTheme Demo" "Demo sub-theme for a CivicTheme theme." ../civictheme_demo

EOF;
  print PHP_EOL;
}

/**
 * Print footer.
 */
function print_footer(string $name, string $machine_name, string $path): void {
  print <<<EOF

  {$name} ({$machine_name}) sub-theme was created successfully in "{$path}".

  NEXT STEPS
  ----------

  Ensure that front-end assets can be built:

    cd {$path}
    npm install
    npm run build
    npm run storybook

  Enable theme in Drupal:

    drush theme:enable civictheme -y
    drush config-set system.theme default civictheme
    drush theme:enable {$machine_name} -y
    drush config-set system.theme default {$machine_name}

EOF;
  print PHP_EOL;
}

/**
 * Validate theme machine name.
 *
 * @SuppressWarnings(PHPMD.MissingImport)
 */
function validate_theme_machine_name(string $name): void {
  if (!preg_match('/^[a-z][a-z_0-9]*$/', $name)) {
    throw new \RuntimeException('Invalid machine name. Theme machine name can only start with lowercase letters and contain lowercase letters, numbers and underscores.');
  }
}

/**
 * Prepare theme stub files.
 *
 * @return string
 *   Path to stub directory.
 */
function prepare_stub(): string {
  $tmp_dir = file_tempdir();
  $starter_kit_dir = find_starter_kit_dir();
  file_copy_recursively($starter_kit_dir, $tmp_dir, file_ignore_paths());

  return $tmp_dir;
}

/**
 * Process stub directory.
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
function process_stub(string $dir, array $options): void {
  $machine_name_hyphenated = str_replace('_', '-', (string) $options['machine_name']);
  // @formatter:off
  // phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
  // phpcs:disable Drupal.WhiteSpace.Comma.TooManySpaces
  file_replace_dir_content('civictheme_starter_kit',              $options['machine_name'], $dir);
  file_replace_dir_content('civictheme-starter-kit',              $machine_name_hyphenated, $dir);
  file_replace_dir_content('CivicTheme Starter Kit description.', $options['description'],  $dir);
  file_replace_dir_content('CivicTheme Starter Kit',              $options['name'],         $dir);

  file_replace_string_filename('civictheme_starter_kit',          $options['machine_name'], $dir);
  // @formatter:on
  // phpcs:enable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
  // phpcs:enable Drupal.WhiteSpace.Comma.TooManySpaces

  // Resolve CivicTheme's location relative to the new theme.
  $current_dir = __DIR__;
  $relative_dir = file_get_relative_dir($options['path'], $current_dir);
  file_replace_file_content('../../contrib/civictheme/', $relative_dir, $dir . '/' . 'build.js');
  file_replace_file_content('../../../contrib/civictheme/', $relative_dir, $dir . '/' . 'package.json');

  // Adjust per-file settings.
  //
  // Remove 'hidden: true' from the info.
  $info_file = $dir . DIRECTORY_SEPARATOR . $options['machine_name'] . '.info.yml';
  if (file_exists($info_file)) {
    $content = file_get_contents($info_file) ?: '';
    $content = str_replace("hidden: true\n", '', $content);
    file_put_contents($info_file, $content);
  }

  // Add current CivicTheme information to the composer.json.
  $composerjson_file = $dir . DIRECTORY_SEPARATOR . 'composer.json';
  if (file_exists($composerjson_file)) {
    $composerjson = find_composerjson($dir);
    $composerjson['extra']['civictheme']['version'] = $options['composerjson']['version'] ?? '1.0.0';
    $composerjson['extra']['civictheme']['homepage'] = $options['composerjson']['homepage'];
    $composerjson['extra']['civictheme']['support']['issues'] = $options['composerjson']['support']['issues'];
    $composerjson['extra']['civictheme']['support']['source'] = $options['composerjson']['support']['source'];
    $composerjson_encoded = json_encode($composerjson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($composerjson_file, $composerjson_encoded);
  }

  // Add current CivicTheme information to the package.json.
  $packagejson_file = $dir . DIRECTORY_SEPARATOR . 'package.json';
  if (file_exists($packagejson_file)) {
    $packagejson = find_packagejson($dir);
    $packagejson['civictheme']['version'] = $options['packagejson']['version'] ?? '1.0.0';
    $packagejson['civictheme']['homepage'] = $options['packagejson']['homepage'];
    $packagejson['civictheme']['bugs'] = $options['packagejson']['bugs'];
    $packagejson['civictheme']['repository'] = $options['packagejson']['repository'];
    $packagejson_encoded = preg_replace_callback('/^ +/m', static function (array $m): string {
      return str_repeat(' ', strlen((string) ceil((int) $m[0] / 2)));
    }, (string) json_encode($packagejson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    file_put_contents($packagejson_file, $packagejson_encoded);
  }

  // Remove all the examples component before moving to the final path.
  if ($options['remove_examples']) {
    $example_components = example_component_paths();
    foreach ($example_components as $example_dir) {
      file_remove_dir($dir . DIRECTORY_SEPARATOR . $example_dir);
    }
  }
}

/**
 * Find starter kit directory.
 *
 * @return string
 *   Path to the Starter Kit directory.
 *
 * @throws \Exception
 *   If directory does not exist.
 *
 * @SuppressWarnings(PHPMD.MissingImport)
 */
function find_starter_kit_dir() {
  $dir = __DIR__ . DIRECTORY_SEPARATOR . 'civictheme_starter_kit';

  if (!file_exists($dir)) {
    throw new \Exception('Unable to find CivicTheme starter kit location.');
  }

  return $dir;
}

/**
 * Find composer.json.
 *
 * @param string $dir
 *   Directory to search in.
 *
 * @return array<string, mixed>
 *   Decoded composer.json.
 *
 * @SuppressWarnings(PHPMD.MissingImport)
 */
function find_composerjson(string $dir): array {
  $filepath = $dir . DIRECTORY_SEPARATOR . 'composer.json';

  if (!file_exists($filepath)) {
    throw new \Exception('Unable to find CivicTheme composer.json location.');
  }

  return json_decode((string) file_get_contents($filepath), TRUE);
}

/**
 * Find package.json.
 *
 * @param string $dir
 *   Directory to search in.
 *
 * @return array<string, mixed>
 *   Decoded package.json.
 *
 * @SuppressWarnings(PHPMD.MissingImport)
 */
function find_packagejson(string $dir): array {
  $filepath = $dir . DIRECTORY_SEPARATOR . 'package.json';

  if (!file_exists($filepath)) {
    throw new \Exception('Unable to find CivicTheme package.json location.');
  }

  return json_decode((string) file_get_contents($filepath), TRUE);
}

// ////////////////////////////////////////////////////////////////////////// //
//                        FILE MANIPULATORS                                   //
// ////////////////////////////////////////////////////////////////////////// //

/**
 * Recursively copy files and directories.
 *
 * The contents of $src will be copied as the contents of $dst.
 *
 * @param string $src
 *   Source directory to copy from.
 * @param string $dst
 *   Destination directory to copy to.
 * @param array $exclude
 *   Optional array of entries to exclude.
 * @param int $permissions
 *   Permissions to set on created directories. Defaults to 0755.
 * @param bool $copy_empty_dirs
 *   Flag to copy empty directories. Defaults to FALSE.
 *
 * @return bool
 *   TRUE if the result of copy was successful, FALSE otherwise.
 *
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
function file_copy_recursively(string $src, string $dst, array $exclude = [], $permissions = 0755, $copy_empty_dirs = FALSE): bool {
  $parent = dirname($dst);

  if (empty($parent)) {
    throw new \Exception(sprintf('Unable to get parent directory of "%s".', $dst));
  }

  if (!is_dir($parent)) {
    mkdir($parent, $permissions, TRUE);
  }

  // Note that symlink target must exist.
  if (is_link($src)) {
    // Changing dir symlink will be relevant to the current destination's file
    // directory.
    $cur_dir = getcwd();

    if (!$cur_dir) {
      throw new \Exception('Unable to get current working directory.');
    }

    chdir($parent);

    $ret = TRUE;
    if (!is_readable(basename($dst))) {
      if (empty(readlink($src))) {
        throw new \Exception(sprintf('Symlink target "%s" does not exist.', $src));
      }

      $ret = symlink(readlink($src), basename($dst));
    }

    chdir($cur_dir);

    return $ret;
  }

  if (is_file($src)) {
    $ret = copy($src, $dst);
    if ($ret) {
      $perms = fileperms($src);
      if ($perms === FALSE) {
        throw new \Exception(sprintf('Unable to get permissions of "%s".', $src));
      }

      chmod($dst, $perms);
    }

    return $ret;
  }

  if (!is_dir($dst) && $copy_empty_dirs) {
    mkdir($dst, $permissions, TRUE);
  }

  $dir = dir($src);
  while ($dir && FALSE !== $entry = $dir->read()) {
    if ($entry == '.' || $entry == '..' || in_array($entry, $exclude)) {
      continue;
    }

    file_copy_recursively($src . DIRECTORY_SEPARATOR . $entry, $dst . DIRECTORY_SEPARATOR . $entry, $exclude, $permissions, $copy_empty_dirs);
  }

  if ($dir) {
    $dir->close();
  }

  return TRUE;
}

/**
 * Replace file content.
 */
function file_replace_file_content(string $needle, string $replacement, string $filename): void {
  if (!is_readable($filename) || file_is_excluded_from_processing($filename)) {
    return;
  }

  $content = file_get_contents($filename);

  $replaced = is_regex($needle)
    ? preg_replace($needle, $replacement, (string) $content)
    : str_replace($needle, $replacement, (string) $content);

  if ($replaced != $content) {
    file_put_contents($filename, $replaced);
  }
}

/**
 * Replace directory content.
 */
function file_replace_dir_content(string $needle, string $replacement, string $dir): void {
  $files = file_scandir_recursive($dir, file_ignore_paths());
  foreach ($files as $filename) {
    file_replace_file_content($needle, $replacement, $filename);
  }
}

/**
 * Replace a string in the file name.
 */
function file_replace_string_filename(string $search, string $replace, string $dir): void {
  $files = file_scandir_recursive($dir, file_ignore_paths());
  foreach ($files as $filename) {
    $new_filename = str_replace($search, $replace, $filename);
    if ($filename != $new_filename) {
      $new_dir = dirname($new_filename);
      if (!is_dir($new_dir)) {
        mkdir($new_dir, 0777, TRUE);
      }

      rename($filename, $new_filename);
    }
  }
}

/**
 * Remove directory.
 */
function file_remove_dir(string $dir): void {
  if (is_dir($dir)) {
    $files = file_scandir_recursive($dir, [], TRUE);
    foreach ($files as $file) {
      if (is_dir($file)) {
        file_remove_dir($file);
        continue;
      }

      if (file_exists($file)) {
        unlink($file);
      }
    }

    rmdir($dir);
  }
}

/**
 * Recursively scan directory for files.
 *
 * @param string $dir
 *   Directory to scan.
 * @param array $ignore_paths
 *   Optional array of paths to ignore.
 * @param bool $include_dirs
 *   Optional flag to include directories in the result.
 *
 * @return array<string>
 *   Array of discovered files.
 *
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
function file_scandir_recursive(string $dir, array $ignore_paths = [], bool $include_dirs = FALSE): array {
  $discovered = [];

  if (is_dir($dir)) {
    $paths = array_diff(scandir($dir) ?: [], ['.', '..']);

    foreach ($paths as $path) {
      $path = $dir . '/' . $path;

      foreach ($ignore_paths as $ignore_path) {
        // Exclude based on sub-path match.
        if (str_contains($path, (string) $ignore_path)) {
          continue(2);
        }
      }

      if (is_dir($path)) {
        if ($include_dirs) {
          $discovered[] = $path;
        }

        $discovered = array_merge($discovered, file_scandir_recursive($path, $ignore_paths, $include_dirs));
        continue;
      }

      $discovered[] = $path;
    }
  }

  return $discovered;
}

/**
 * Ignore path.
 *
 * @return array<string>
 *   Array of paths to ignore.
 */
function file_ignore_paths(): array {
  return array_merge([
    '.git',
    '.idea',
    '.components-civictheme',
    '.data',
    'components_combined',
    'dist',
    'node_modules',
    'storybook-static',
    'vendor',
  ], file_internal_paths());
}

/**
 * Internal paths to ignore.
 *
 * @return array<string>
 *   Array of internal paths.
 */
function file_internal_paths(): array {
  return [];
}

/**
 * Example component paths.
 *
 * @return array<string>
 *   Array of example component paths.
 */
function example_component_paths(): array {
  return [
    'components/01-atoms/demo-button',
    'components/02-molecules/navigation-card',
    'components/03-organisms/header',
  ];
}

/**
 * Check if the file is excluded from the processing.
 */
function file_is_excluded_from_processing(string $filename): bool {
  $excluded_patterns = [
    '.+\.png',
    '.+\.jpg',
    '.+\.jpeg',
    '.+\.bpm',
    '.+\.tiff',
  ];

  return (bool) preg_match('/^(' . implode('|', $excluded_patterns) . ')$/', $filename);
}

/**
 * Creates a random unique temporary directory.
 *
 * @SuppressWarnings(PHPMD.MissingImport)
 */
function file_tempdir(string $dir = NULL, string $prefix = 'tmp_', int $mode = 0700, int $max_attempts = 1000): string {
  if (is_null($dir)) {
    $dir = sys_get_temp_dir();
  }

  $dir = rtrim($dir, DIRECTORY_SEPARATOR);

  if (!is_dir($dir) || !is_writable($dir)) {
    throw new \RuntimeException(sprintf('Unable to find writable temporary directory "%s".', $dir));
  }

  if (strpbrk($prefix, '\\/:*?"<>|') !== FALSE) {
    throw new \RuntimeException(sprintf('The prefix "%s" contains invalid characters.', $prefix));
  }

  $attempts = 0;

  do {
    $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
  } while (!mkdir($path, $mode) && $attempts++ < $max_attempts);

  if (!is_dir($path) || !is_writable($path)) {
    throw new \RuntimeException(sprintf('Unable to create temporary directory "%s".', $path));
  }

  return $path;
}

/**
 * Get relative directory path from 2 directory paths.
 *
 * This function does not rely on existence of paths.
 *
 * @param string $dir1
 *   First dir path to compare.
 * @param string $dir2
 *   Second dir path to compare.
 *
 * @return string
 *   Relative path between 2 directories.
 */
function file_get_relative_dir(string $dir1, string $dir2): string {
  $dir1 = rtrim($dir1, '/') . '/';
  $dir2 = rtrim($dir2, '/') . '/';

  if ($dir1 === $dir2) {
    return './';
  }

  $dir1 = explode('/', $dir1);
  $dir2 = explode('/', $dir2);
  $parts = $dir2;

  foreach ($dir1 as $depth => $dir) {
    if ($dir === $dir2[$depth]) {
      array_shift($parts);
      continue;
    }

    $remaining = count($dir1) - $depth;
    if ($remaining > 1) {
      $parts = array_pad($parts, -1 * (count($parts) + $remaining - 1), '..');
      break;
    }

    $parts[0] = './' . $parts[0];
  }

  return implode('/', $parts);
}

/**
 * Canonicalize the path by removing './' and '../'.
 *
 * @param string $path
 *   Path that need to be canonicalized.
 *
 * @return string
 *   Path with all '.', '..', './' and '../' removed.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc3986#section-5.2.4
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.IfStatementAssignment)
 */
function file_path_canonicalize(string $path): string {
  $output = '';

  while ($path !== '') {
    if (
      ($prefix = substr($path, 0, 3)) === '../' ||
      ($prefix = substr($path, 0, 2)) === './'
    ) {
      $path = substr($path, strlen($prefix));
      continue;
    }

    if (
      ($prefix = substr($path, 0, 3)) === '/./' ||
      ($prefix = $path) === '/.'
    ) {
      $path = '/' . substr($path, strlen($prefix));
      continue;
    }

    if (
      ($prefix = substr($path, 0, 4)) === '/../' ||
      ($prefix = $path) === '/..'
    ) {
      $path = '/' . substr($path, strlen($prefix));
      $output = substr($output, 0, (int) strrpos($output, '/'));
      continue;
    }

    if ($path === '.' || $path === '..') {
      $path = '';
      continue;
    }

    $pos = strpos($path, '/');
    if ($pos === 0) {
      $pos = strpos($path, '/', 1);
    }

    if ($pos === FALSE) {
      $pos = strlen($path);
    }

    $output .= substr($path, 0, $pos);
    $path = substr($path, $pos);
  }

  return $output;
}

// ////////////////////////////////////////////////////////////////////////// //
//                                HELPERS                                     //
// ////////////////////////////////////////////////////////////////////////// //

/**
 * Check if the provided string is a regular expression.
 */
function is_regex(string $str): bool {
  if (preg_match('/^(.{3,}?)[imsxuADU]*$/', $str, $m)) {
    $start = substr($m[1], 0, 1);
    $end = substr($m[1], -1);

    if ($start === $end) {
      return !preg_match('/[*?[:alnum:] \\\\]/', $start);
    }

    foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
      if ($start === $delimiters[0] && $end === $delimiters[1]) {
        return TRUE;
      }
    }
  }

  return FALSE;
}

/**
 * Show a verbose message.
 */
function verbose(): void {
  if (getenv('SCRIPT_QUIET') != '1') {
    print sprintf(...func_get_args()) . PHP_EOL;
  }
}

// ////////////////////////////////////////////////////////////////////////// //
//                                ENTRYPOINT                                  //
// ////////////////////////////////////////////////////////////////////////// //

ini_set('display_errors', 1);

if (PHP_SAPI !== 'cli' || !empty($_SERVER['REMOTE_ADDR'])) {
  die('This script can be only ran from the command line.');
}

// Allow to skip the script run.
if (getenv('SCRIPT_RUN_SKIP') != 1) {
  // Custom error handler to catch errors based on set ERROR_LEVEL.
  // @phpstan-ignore-next-line
  set_error_handler(static function ($severity, $message, $file, $line): void {
    if ((error_reporting() & $severity) === 0) {
      // This error code is not included in error_reporting.
      return;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
  });

  try {
    $code = main($argv, $argc);
    exit($code);
  }
  catch (\ErrorException $exception) {
    if ($exception->getSeverity() <= ERROR_LEVEL) {
      print PHP_EOL . 'RUNTIME ERROR: ' . $exception->getMessage() . PHP_EOL;
      exit($exception->getCode() == 0 ? EXIT_ERROR : $exception->getCode());
    }
  }
  catch (\Exception $exception) {
    print PHP_EOL . 'ERROR: ' . $exception->getMessage() . PHP_EOL;
    exit($exception->getCode() == 0 ? EXIT_ERROR : $exception->getCode());
  }
}
