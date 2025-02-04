// phpcs:ignoreFile
/*
Civictheme build - version 0.0.1 (alpha).

This is a simplified build system for civictheme, designed to:

- Speed up build time
- Improve auditability by reducing dependencies / external code

Notes:

- No Windows support (unless through WSL).
- This relies on command line rsync.
- This does not support uglify or minifying JS / CSS code.
- Watch does not trigger on a directory change, only on a (scss, twig, js) file change.
*/

import fs from 'fs'
import path from 'path'
import { globSync } from 'glob'
import { execSync, spawn } from 'child_process'
import * as sass from 'sass-embedded'

// ----------------------------------------------------------------------------- CONFIG AND START

const config = {
  build: false,
  watch: false,
  cli: false,
  lintex: false,
  combine: false,
  styles: false,
  styles_editor: false,
  styles_admin: false,
  styles_layout: false,
  styles_variables: false,
  styles_stories: false,
  js_drupal: false,
  js_storybook: false,
  assets: false,
  constants: false,
  base: false,
}

const flags = process.argv.slice(2)
if (['cli', 'lintex'].indexOf(flags[0]) >= 0) {
  config[flags[0]] = true
} else {
  const buildType = ['build', 'watch']
  const buildWatchFlagCount = flags?.filter(f => buildType.indexOf(f) >= 0).length

  if (flags.length <= 2 && buildWatchFlagCount <= 2) {
    // If build and/or watch, or neither..
    config.build = buildWatchFlagCount === 0 || flags.indexOf('build') >= 0
    config.watch = flags.indexOf('watch') >= 0
    config.combine = true
    config.styles = true
    config.styles_editor = true
    config.styles_variables = true
    config.styles_stories = true
    config.js_drupal = true
    config.js_storybook = true
    config.assets = true
    config.constants = true
  } else {
    // Fully configured from command line - everything disabled by default.
    flags.forEach((flag) => {
      config[flag] = true
    })
  }
}

let startTime = new Date().getTime()
let lastTime = null

const PATH = import.meta.dirname

const THEME_NAME                = PATH.split('/').reverse()[0]
const DIR_CIVICTHEME            = fullPath('../../contrib/civictheme/')
const DIR_COMPONENTS_IN         = fullPath('./components/')
const DIR_COMPONENTS_OUT        = fullPath('./components_combined/')
const DIR_UIKIT_COMPONENTS_IN   = `${DIR_CIVICTHEME}/components/`
const DIR_UIKIT_COPY_OUT        = fullPath('./.components-civictheme/')
const DIR_OUT                   = fullPath('./dist/')
const DIR_ASSETS_IN             = fullPath('./assets/')
const DIR_ASSETS_OUT            = fullPath('./dist/assets/')

const COMPONENT_DIR             = config.base ? DIR_COMPONENTS_IN : DIR_COMPONENTS_OUT
const STYLE_NAME                = config.base ? 'civictheme' : 'styles'
const SCRIPT_NAME               = config.base ? 'civictheme' : 'scripts'

const STYLE_FILE_IN             = `${COMPONENT_DIR}/style.scss`
const STYLE_VARIABLE_FILE_IN    = `${COMPONENT_DIR}/style.css_variables.scss`
const STYLE_STORIES_FILE_IN     = `${COMPONENT_DIR}/style.stories.scss`
const STYLE_THEME_FILE_IN       = `${DIR_ASSETS_IN}/sass/theme.scss`
const STYLE_EDITOR_FILE_IN      = `${DIR_ASSETS_IN}/sass/theme.editor.scss`
const STYLE_ADMIN_FILE_IN       = `${DIR_ASSETS_IN}/sass/theme.admin.scss`
const STYLE_LAYOUT_FILE_IN      = `${DIR_ASSETS_IN}/sass/theme.layout.scss`
const STYLE_FILE_OUT            = `${DIR_OUT}/${STYLE_NAME}.css`
const STYLE_EDITOR_FILE_OUT     = `${DIR_OUT}/${STYLE_NAME}.editor.css`
const STYLE_VARIABLE_FILE_OUT   = `${DIR_OUT}/${STYLE_NAME}.variables.css`
const STYLE_ADMIN_FILE_OUT      = `${DIR_OUT}/${STYLE_NAME}.admin.css`
const STYLE_LAYOUT_FILE_OUT     = `${DIR_OUT}/${STYLE_NAME}.layout.css`
const STYLE_STORIES_FILE_OUT    = `${DIR_OUT}/${STYLE_NAME}.stories.css`

const VAR_CT_ASSETS_DIRECTORY   = `$ct-assets-directory: '/themes/custom/${THEME_NAME}/dist/assets/';`

const JS_FILE_OUT               = `${DIR_OUT}/${SCRIPT_NAME}.js`
const JS_STORYBOOK_FILE_OUT     = `${DIR_OUT}/${SCRIPT_NAME}.storybook.js`
const JS_CIVIC_IMPORTS          = `${COMPONENT_DIR}/**/!(*.stories|*.stories.data|*.component|*.min|*.test|*.script|*.utils).js`
const JS_LIB_IMPORTS            = [fullPath('./node_modules/@popperjs/core/dist/umd/popper.js')]
const JS_ASSET_IMPORTS          = [
                                    `${DIR_CIVICTHEME}/assets/js/**/*.js`,
                                    `${DIR_ASSETS_IN}/js/**/*.js`,
                                  ]
const JS_LINT_EXCLUSION_HEADER  = '// phpcs:ignoreFile'

const CONSTANTS_FILE_OUT        = `${DIR_OUT}/constants.json`
const CONSTANTS_SCSS_IMPORTER   = fullPath(`./.storybook/importer.scss_variables.js`)
const CONSTANTS_BACKGROUND_UTIL = `${COMPONENT_DIR}/00-base/background/background.utils.js`
const CONSTANTS_ICON_UTIL       = `${COMPONENT_DIR}/00-base/icon/icon.utils.js`
const CONSTANTS_LOGO_UTIL       = `${COMPONENT_DIR}/02-molecules/logo/logo.utils.js`

if (config.build) {
  build()
}

if (config.watch) {
  watch()
}

if (config.cli) {
  cli()
}

if (config.lintex) {
  lintExclusions()
}

// ----------------------------------------------------------------------------- BUILD STEPS

function buildOutDirectory() {
  if (!fs.existsSync(DIR_OUT)) {
    fs.mkdirSync(DIR_OUT)
  }
}

function buildCombineDirectories() {
  if (config.combine && !config.base) {
    runCommand(`rsync -a --delete ${DIR_UIKIT_COMPONENTS_IN}/ ${DIR_UIKIT_COPY_OUT}/`)
    runCommand(`rsync -a --delete ${DIR_UIKIT_COPY_OUT}/ ${DIR_COMPONENTS_OUT}/`)
    runCommand(`rsync -a ${DIR_COMPONENTS_IN}/ ${DIR_COMPONENTS_OUT}/`)
    console.log(`Saved: Combined folders ${time()}`)
  }
}

function buildStyles() {
  if (config.styles) {
    const stylecss = [
      VAR_CT_ASSETS_DIRECTORY,
      loadStyleFile(STYLE_FILE_IN, COMPONENT_DIR),
      config.styles_variables ? getImportsFromGlob(STYLE_VARIABLE_FILE_IN, COMPONENT_DIR) : '',
      getImportsFromGlob(STYLE_THEME_FILE_IN, PATH),
      config.base ? [
        config.styles_admin ? getImportsFromGlob(STYLE_ADMIN_FILE_IN, PATH) : '',
        config.styles_layout ? loadStyleFile(STYLE_LAYOUT_FILE_IN, PATH) : '',
      ].join('\n') : '',
    ].join('\n')

    const compiled = sass.compileString(stylecss, { loadPaths: [COMPONENT_DIR, PATH] })
    const compiledImportAtTop = compiled.css.split('\n')
      .sort(a => a.indexOf('@import') === 0 ? -1 : 0)
      .sort(a => a.indexOf('@charset') === 0 ? -1 : 0)
      .join('\n')
    fs.writeFileSync(STYLE_FILE_OUT, compiledImportAtTop, 'utf-8')
    console.log(`Saved: Component styles ${time()}`)
  }
}

function buildStylesEditor() {
  if (config.styles_editor) {
    const editorcss = [
      VAR_CT_ASSETS_DIRECTORY,
      loadStyleFile(STYLE_EDITOR_FILE_IN, PATH),
    ].join('\n')

    const compiled = sass.compileString(editorcss, { loadPaths: [PATH] })
    fs.writeFileSync(STYLE_EDITOR_FILE_OUT, compiled.css, 'utf-8')
    console.log(`Saved: Editor styles ${time()}`)
  }
}

function buildStylesAdmin() {
  if (config.styles_admin) {
    const compiled = sass.compile(STYLE_ADMIN_FILE_IN, { loadPaths: [PATH] })
    fs.writeFileSync(STYLE_ADMIN_FILE_OUT, compiled.css, 'utf-8')
    console.log(`Saved: Admin styles ${time()}`)
  }
}

function buildStylesLayout() {
  if (config.styles_layout) {
    const layoutcss = [
      VAR_CT_ASSETS_DIRECTORY,
      loadStyleFile(STYLE_LAYOUT_FILE_IN, PATH),
    ].join('\n')

    const compiled = sass.compileString(layoutcss, { loadPaths: [PATH] })
    fs.writeFileSync(STYLE_LAYOUT_FILE_OUT, compiled.css, 'utf-8')
    console.log(`Saved: Layout styles ${time()}`)
  }
}

function buildStylesVariables() {
  if (config.styles_variables) {
    const compiled = sass.compile(STYLE_VARIABLE_FILE_IN, { loadPaths: [COMPONENT_DIR] })
    fs.writeFileSync(STYLE_VARIABLE_FILE_OUT, compiled.css, 'utf-8')
    console.log(`Saved: Variable styles ${time()}`)
  }
}

function buildStylesStories() {
  if (config.styles_stories) {
    const storybookcss = [
      VAR_CT_ASSETS_DIRECTORY,
      loadStyleFile(STYLE_STORIES_FILE_IN, COMPONENT_DIR),
    ].join('\n')

    const compiled = sass.compileString(storybookcss, { loadPaths: [COMPONENT_DIR, PATH] })
    fs.writeFileSync(STYLE_STORIES_FILE_OUT, compiled.css, 'utf-8')
    console.log(`Saved: Stories styles ${time()}`)
  }
}

function buildJavascript() {
  if (config.js_drupal || config.js_storybook) {
    const jsComponents = []
    const jsOutData = []

    // Add header.
    jsOutData.push(JS_LINT_EXCLUSION_HEADER)

    // Third party imports.
    JS_LIB_IMPORTS.forEach(filename => {
      jsOutData.push(stripJS(fs.readFileSync(filename, 'utf-8')))
    })

    // Civictheme asset imports.
    globSync(JS_ASSET_IMPORTS).forEach(filename => {
      jsOutData.push(stripJS(fs.readFileSync(filename, 'utf-8')))
    })

    // Civictheme component imports.
    globSync(JS_CIVIC_IMPORTS).forEach(filename => {
      const name = `${THEME_NAME}_${filename.split('/').reverse()[0].replace('.js', '').replace(/-/g, '_')}`
      const body = fs.readFileSync(filename, 'utf-8')
      jsComponents.push({ name, body })
    })

    // Write JS file with drupal behaviour wrapper.
    if (config.js_drupal) {
      fs.writeFileSync(JS_FILE_OUT, [
        ...jsOutData,
        ...jsComponents.map(i => {
          return `Drupal.behaviors.${i.name} = {attach: function (context, settings) {\n${i.body}\n}};`
        })
      ].join('\n'), 'utf-8')
      console.log(`Saved: Compiled javascript (drupal) ${time()}`)
    }

    // Write JS file with dom content loaded wrapper.
    if (config.js_storybook) {
      fs.writeFileSync(JS_STORYBOOK_FILE_OUT, [
        ...jsOutData,
        ...jsComponents.map(i => {
          return `document.addEventListener('DOMContentLoaded', () => {\n${i.body}\n});`
        })
      ].join('\n'), 'utf-8')
      console.log(`Saved: Compiled javascript (storybook) ${time()}`)
    }
  }
}

function buildAssetsDirectory() {
  if (config.assets) {
    runCommand(`rsync -a --delete --prune-empty-dirs --exclude .gitkeep --exclude js --exclude sass ${DIR_ASSETS_IN}/ ${DIR_ASSETS_OUT}/`)
    console.log(`Saved: Assets ${time()}`)
  }
}

async function buildConstants() {
  if (config.constants) {
    const { default: scssVariableImporter } = await import(CONSTANTS_SCSS_IMPORTER);
    const { default: backgroundUtils } = await import(CONSTANTS_BACKGROUND_UTIL);
    const { default: iconUtils } = await import(CONSTANTS_ICON_UTIL);
    const { default: logoUtils } = await import(CONSTANTS_LOGO_UTIL);

    const constants = {
      BACKGROUNDS: backgroundUtils.getBackgrounds(),
      ICONS: iconUtils.getIcons(),
      LOGOS: logoUtils.getLogos(),
      SCSS_VARIABLES: scssVariableImporter.getVariables(),
    }
    fs.writeFileSync(CONSTANTS_FILE_OUT, JSON.stringify(constants, null, 2), 'utf-8')
    console.log(`Saved: Compiled constants ${time()}`)
  }
}

// ----------------------------------------------------------------------------- CORE FEATURES

async function build() {
  startTime = new Date().getTime()
  lastTime = startTime

  buildOutDirectory()
  buildCombineDirectories()
  buildStyles()
  buildStylesEditor()
  buildStylesAdmin()
  buildStylesLayout()
  buildStylesVariables()
  buildStylesStories()
  buildJavascript()
  buildAssetsDirectory()
  await buildConstants()

  console.log(`Time taken: ${time(true)}`)
}

function watch() {
  console.log(`Watching: ${path.relative(PATH, DIR_COMPONENTS_IN)}/`)
  const supportedExtensions = ['scss', 'js', 'twig']
  let timeout = null
  const watcher = fs.watch(DIR_COMPONENTS_IN, { recursive: true }, (event, filename) => {
    const ext = filename.split('.').pop()
    if (supportedExtensions.indexOf(ext) >= 0) {
      clearTimeout(timeout)
      timeout = setTimeout(build, 300)
    }
  })
}

function cli() {
  const scripts = flags.slice(1)
  console.log(`Running npm commands: ${scripts.join(' && ')}`)
  scripts.forEach((script, idx) => {
    const child = spawn('npm', ['run', script])
    child.stdout.on('data', (data) => {
      const color = `\x1b[3${Math.min(idx, 3) + 3}m`
      const sData = data.toString().replaceAll(/[\u001b\u009b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/g, '') // strip ANSI colours
      process.stdout.write(`${color}${sData}`)
    })
  })
}

function lintExclusions() {
  const storybookStaticPath = fullPath('./storybook-static/**/*.js')
  console.log(`Applying lint exclusions: ${storybookStaticPath}`)
  const header = `${JS_LINT_EXCLUSION_HEADER}\n`
  globSync(storybookStaticPath).forEach(filename => {
    const data = fs.readFileSync(filename, 'utf-8')
    if (data.substr(0, header.length) !== header) {
      fs.writeFileSync(filename, `${header}${data}`, 'utf-8')
    }
  })
}

// ----------------------------------------------------------------------------- UTILITIES

function runCommand(command) {
  execSync(command, (error, stdout, stderr) => {
    if (error) {
      console.log(`error: ${error.message}`)
      return
    }
    if (stderr) {
      console.log(`stderr: ${stderr}`)
      return
    }
    if (stdout) {
      console.log(stdout)
    }
  })
}

function getImportsFromGlob(path, cwd) {
  return globSync(path, { cwd }).sort((a, b) => a.localeCompare(b)).map(i => `@import "${i}";`).join('\n')
}

function loadStyleFile(path, cwd) {
  const result = []
  let data = fs.readFileSync(path, 'utf-8')

  data.split('\n').forEach(line => {
    // Only glob imports with *!()| characters.
    const match = /@import '(.*[\*!()|].*)'/.exec(line)
    result.push(match ? getImportsFromGlob(match[1], cwd) : line)
  })

  return result.join('\n')
}

function stripJS(js) {
  return js.replace(/\/\/# sourceMappingURL=.*\.(map|json)/gi, '')
}

function fullPath(filepath) {
  return path.resolve(PATH, filepath)
}

function time(full) {
  const now = new Date().getTime()
  const rtn = now - (full ? startTime : lastTime)
  lastTime = now
  return `[ ${rtn} ms ]`
}
