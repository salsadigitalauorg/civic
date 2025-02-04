<?php

declare(strict_types=1);

/**
 * Class CivicthemeCreateSubthemeScriptUnitTest.
 *
 * Unit tests for civictheme_create_subtheme.php.
 *
 * @group site:unit
 * @group scripts
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
class CivicthemeCreateSubthemeScriptUnitTest extends ScriptUnitTestBase {

  /**
   * {@inheritdoc}
   */
  protected $script = 'themes/contrib/civictheme/civictheme_create_subtheme.php';

  /**
   * CivicTheme dir.
   *
   * @var string
   */
  protected $civicthemeDir = 'themes/contrib/civictheme';

  /**
   * @dataProvider dataProviderMain
   * @runInSeparateProcess
   */
  public function testMain(string|array $args, int $expected_code, string $expected_output): void {
    $args = is_array($args) ? $args : [$args];
    $result = $this->runScript($args, TRUE);
    $this->assertEquals($expected_code, $result['code']);
    $this->assertStringContainsString($expected_output, $result['output']);
  }

  public static function dataProviderMain(): array {
    return [
      [
        '--help',
        0,
        'CivicTheme Starter Kit scaffolding',
      ],
      [
        '-help',
        0,
        'CivicTheme Starter Kit scaffolding',
      ],
      [
        '-h',
        0,
        'CivicTheme Starter Kit scaffolding',
      ],
      [
        '-?',
        0,
        'CivicTheme Starter Kit scaffolding',
      ],
      [
        [],
        1,
        'CivicTheme Starter Kit scaffolding',
      ],
      [
        [1, 2],
        1,
        'CivicTheme Starter Kit scaffolding',
      ],
      [
        [1, 2, 3, 4, 5, 6, 7],
        1,
        'CivicTheme Starter Kit scaffolding',
      ],
    ];
  }

  /**
   * @dataProvider dataProviderTestLocation
   * @runInSeparateProcess
   */
  public function testLocation(string $civictheme_dir, string $newtheme_rel_dir, string $expected_newtheme_dir, string $expected_rel_path): void {
    $newtheme_name = 'new_theme';

    $sut_dir = $this->prepareSut($civictheme_dir);

    $composerjson_file = $sut_dir . '/composer.json';
    $composerjson = json_decode((string) file_get_contents($composerjson_file), TRUE);
    $composerjson['version'] = '9.8.7';
    $composerjson['homepage'] = 'https://example.com/composer';
    $composerjson['support']['issues'] = 'https://example.com/composer/issues';
    $composerjson['support']['source'] = 'https://example.com/composer/source';
    file_put_contents($composerjson_file, json_encode($composerjson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $packagejson_file = $sut_dir . '/package.json';
    $packagejson = json_decode((string) file_get_contents($packagejson_file), TRUE);
    $packagejson['version'] = '6.5.4';
    $packagejson['homepage'] = 'https://example.com/package';
    $packagejson['bugs'] = 'https://example.com/package/issues';
    $packagejson['repository'] = 'https://example.com/package/source';
    file_put_contents($packagejson_file, json_encode($packagejson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $expected_new_theme_dir_full = $this->tmpDir . '/' . $expected_newtheme_dir;

    $result = $this->runScript([
      $newtheme_name,
      $newtheme_name,
      $newtheme_name,
      $newtheme_rel_dir,
    ], TRUE);
    $this->assertEquals(0, $result['code']);
    $this->assertStringContainsString('sub-theme was created successfully ', $result['output']);
    $this->assertStringContainsString($expected_new_theme_dir_full, $result['output']);

    $expected_new_theme_dir_full .= '/';
    $this->assertDirectoryExists($expected_new_theme_dir_full);

    $this->assertDirectoryExists($expected_new_theme_dir_full . '.storybook');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'assets');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'components');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'templates');
    $this->assertFileExists($expected_new_theme_dir_full . '.eslintignore');
    $this->assertFileExists($expected_new_theme_dir_full . '.eslintrc.yml');
    $this->assertFileExists($expected_new_theme_dir_full . '.gitignore');
    $this->assertFileExists($expected_new_theme_dir_full . '.nvmrc');
    $this->assertFileExists($expected_new_theme_dir_full . '.stylelintrc.json');
    $this->assertFileExists($expected_new_theme_dir_full . $newtheme_name . '.info.yml');
    $this->assertFileExists($expected_new_theme_dir_full . $newtheme_name . '.libraries.yml');
    $this->assertFileExists($expected_new_theme_dir_full . 'build.js');
    $this->assertFileExists($expected_new_theme_dir_full . 'vite.config.js');
    $this->assertFileExists($expected_new_theme_dir_full . 'composer.json');
    $this->assertFileExists($expected_new_theme_dir_full . 'package.json');
    $this->assertFileExists($expected_new_theme_dir_full . 'README.md');
    $this->assertFileExists($expected_new_theme_dir_full . 'screenshot.png');

    $this->assertStringContainsString($expected_rel_path, (string) file_get_contents($expected_new_theme_dir_full . 'build.js'));

    $composerjson_actual = json_decode((string) file_get_contents($expected_new_theme_dir_full . 'composer.json'), TRUE);
    $this->assertStringContainsString($composerjson['version'], $composerjson_actual['extra']['civictheme']['version']);
    $this->assertStringContainsString($composerjson['homepage'], $composerjson_actual['extra']['civictheme']['homepage']);
    $this->assertStringContainsString($composerjson['support']['issues'], $composerjson_actual['extra']['civictheme']['support']['issues']);
    $this->assertStringContainsString($composerjson['support']['source'], $composerjson_actual['extra']['civictheme']['support']['source']);

    $packagejson_actual = json_decode((string) file_get_contents($expected_new_theme_dir_full . 'package.json'), TRUE);
    $this->assertStringContainsString($packagejson['version'], $packagejson_actual['civictheme']['version']);
    $this->assertStringContainsString($packagejson['homepage'], $packagejson_actual['civictheme']['homepage']);
    $this->assertStringContainsString($packagejson['bugs'], $packagejson_actual['civictheme']['bugs']);
    $this->assertStringContainsString($packagejson['repository'], $packagejson_actual['civictheme']['repository']);

    // Examples assertions.
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'components/01-atoms/demo-button');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'components/02-molecules/navigation-card');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'components/03-organisms/header');
  }

  public static function dataProviderTestLocation(): array {
    return [
      // CivicTheme in 'contrib', new theme in 'custom' dir.
      // No new_theme_directory specified - use default one.
      [
        'web/themes/contrib/civictheme',
        '',
        'web/themes/custom/new_theme',
        '../../contrib/civictheme/',
      ],
      // CivicTheme in 'contrib', new theme in 'custom' dir. Same as default.
      [
        'web/themes/contrib/civictheme',
        '../../custom/new_theme',
        'web/themes/custom/new_theme',
        '../../contrib/civictheme/',
      ],
      // CivicTheme in 'contrib', new theme not in 'custom' dir.
      [
        'web/themes/contrib/civictheme',
        '../../new_theme',
        'web/themes/new_theme',
        '../contrib/civictheme/',
      ],
      // CivicTheme not in 'contrib', new theme not in 'custom' dir.
      [
        'web/themes/civictheme',
        '../new_theme',
        'web/themes/new_theme',
        '../civictheme/',
      ],
      // CivicTheme in root, new theme in root dir.
      [
        'civictheme',
        '../new_theme',
        'new_theme',
        '../civictheme/',
      ],
      // CivicTheme in root, new theme in 'custom' dir.
      [
        'civictheme',
        '../custom/new_theme',
        'custom/new_theme',
        '../../civictheme/',
      ],
    ];
  }

  /**
   * @runInSeparateProcess
   */
  public function testExamplesRemoval(): void {
    $civictheme_dir = 'web/themes/contrib/civictheme';
    $newtheme_rel_dir = '';
    $newtheme_name = 'new_theme';
    $expected_newtheme_dir = 'web/themes/custom/new_theme';
    $expected_rel_path = '../../contrib/civictheme/';

    $this->prepareSut($civictheme_dir);
    $expected_new_theme_dir_full = $this->tmpDir . '/' . $expected_newtheme_dir;

    $result = $this->runScript([
      $newtheme_name,
      $newtheme_name,
      $newtheme_name,
      $newtheme_rel_dir,
      '--remove-examples',
    ], TRUE);
    $this->assertEquals(0, $result['code']);
    $this->assertStringContainsString('sub-theme was created successfully ', $result['output']);
    $this->assertStringContainsString($expected_new_theme_dir_full, $result['output']);

    $expected_new_theme_dir_full .= '/';
    $this->assertDirectoryExists($expected_new_theme_dir_full);

    // Standard assertions.
    $this->assertDirectoryExists($expected_new_theme_dir_full . '.storybook');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'assets');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'components');
    $this->assertDirectoryExists($expected_new_theme_dir_full . 'templates');
    $this->assertFileExists($expected_new_theme_dir_full . '.eslintignore');
    $this->assertFileExists($expected_new_theme_dir_full . '.eslintrc.yml');
    $this->assertFileExists($expected_new_theme_dir_full . '.gitignore');
    $this->assertFileExists($expected_new_theme_dir_full . '.nvmrc');
    $this->assertFileExists($expected_new_theme_dir_full . '.stylelintrc.json');
    $this->assertFileExists($expected_new_theme_dir_full . $newtheme_name . '.info.yml');
    $this->assertFileExists($expected_new_theme_dir_full . $newtheme_name . '.libraries.yml');
    $this->assertFileExists($expected_new_theme_dir_full . 'build.js');
    $this->assertFileExists($expected_new_theme_dir_full . 'vite.config.js');
    $this->assertFileExists($expected_new_theme_dir_full . 'package.json');
    $this->assertFileExists($expected_new_theme_dir_full . 'README.md');
    $this->assertFileExists($expected_new_theme_dir_full . 'screenshot.png');

    $this->assertStringContainsString($expected_rel_path, (string) file_get_contents($expected_new_theme_dir_full . 'build.js'));

    // Examples assertions.
    $this->assertDirectoryDoesNotExist($expected_new_theme_dir_full . 'components/01-atoms/demo-button');
    $this->assertDirectoryDoesNotExist($expected_new_theme_dir_full . 'components/02-molecules/navigation-card');
    $this->assertDirectoryDoesNotExist($expected_new_theme_dir_full . 'components/03-organisms/header');
  }

  /**
   * Prepare SUT codebase.
   *
   * @param string $path
   *   Optional path for SUT codebase location.
   *
   * @return string
   *   Path to created SUT codebase.
   */
  protected function prepareSut($path = NULL): string {
    $sut_dir = $this->tmpDir . (empty($path) ? '' : '/' . $path);
    mkdir($sut_dir, 0755, TRUE);

    $this->fileCopyRecursively(getcwd() . '/' . $this->civicthemeDir, $sut_dir, [
      'node_modules',
      'vendor',
      'storybook-static',
      'dist',
    ]);

    $this->script = $sut_dir . '/civictheme_create_subtheme.php';

    return $sut_dir;
  }

  /**
   * @dataProvider dataProviderFileGetRelativeDir
   * @runInSeparateProcess
   */
  public function testFileGetRelativeDir(string $from, string $to, string $expected): void {
    $this->assertEquals($expected, file_get_relative_dir($from, $to));
  }

  public static function dataProviderFileGetRelativeDir(): array {
    return [
      ['/a/b/c/d', '/a/b/c/d', './'],
      ['/a/b/c/d', '/a/b/c', '../'],
      ['/a/b/c/d', '/a/b', '../../'],
      ['/a/b/c/d', '/a/b/c/other', '../other/'],
      ['/a/b/c/d', '/a/b/other2', '../../other2/'],

      ['/a/b/c/d', '/a/b/c/d/', './'],
      ['/a/b/c/d', '/a/b/c/', '../'],
      ['/a/b/c/d', '/a/b/', '../../'],
      ['/a/b/c/d', '/a/b/c/other/', '../other/'],
      ['/a/b/c/d', '/a/b/other2/', '../../other2/'],

      ['/a/b/c/d/', '/a/b/c/d/', './'],
      ['/a/b/c/d/', '/a/b/c/', '../'],
      ['/a/b/c/d/', '/a/b/', '../../'],
      ['/a/b/c/d/', '/a/b/c/other/', '../other/'],
      ['/a/b/c/d/', '/a/b/other2/', '../../other2/'],
    ];
  }

  /**
   * @dataProvider dataProviderFilePathCanonicalize
   * @runInSeparateProcess
   */
  public function testFilePathCanonicalize(string $path, string $expected): void {
    $this->assertEquals($expected, file_path_canonicalize($path));
  }

  public static function dataProviderFilePathCanonicalize(): array {
    return [
      ['', ''],
      ['a', 'a'],

      // Absolute.
      ['/a', '/a'],
      ['/a/b', '/a/b'],

      ['/a/b/.', '/a/b/'],
      ['/a/b/..', '/a/'],
      ['/a/b/c/..', '/a/b/'],
      ['/a/b/c/../d/', '/a/b/d/'],
      ['/a/b/c/../../d/', '/a/d/'],
      ['/a/b/c/../.././d/', '/a/d/'],
      ['/a/b/c/./../.././d/', '/a/d/'],
      ['/a/b/c/././././.././././.././d/', '/a/d/'],
      ['/a/././././b/c/././././.././././.././d/', '/a/d/'],

      ['/a/././././', '/a/'],
      ['/a/./', '/a/'],
      ['/a/./../', '/'],
      ['/a/./../.', '/'],
      ['/a/./.././', '/'],

      ['/a/./../../', '/'],
      ['/a/./../../.', '/'],
      ['/a/./../.././../', '/'],
      ['/a/./../.././../.', '/'],

      // Relative.
      ['a', 'a'],
      ['a/b', 'a/b'],

      ['a/b/.', 'a/b/'],
      ['a/b/..', 'a/'],
      ['a/b/c/..', 'a/b/'],
      ['a/b/c/../d/', 'a/b/d/'],
      ['a/b/c/../../d/', 'a/d/'],
      ['a/b/c/../.././d/', 'a/d/'],
      ['a/b/c/./../.././d/', 'a/d/'],
      ['a/b/c/././././.././././.././d/', 'a/d/'],
      ['a/././././b/c/././././.././././.././d/', 'a/d/'],

      ['a/././././', 'a/'],
      ['a/./', 'a/'],
      ['a/./../', '/'],
      ['a/./../.', '/'],
      ['a/./.././', '/'],

      ['a/./../../', '/'],
    ];
  }

}
