# CivicTheme Drupal theme development

This document provides instructions on how to interact with this development
repository.

Please refer to the primary [README.md](README.md#local-environment-setup) for
detailed information on setting up your local development environment.

Before starting, ensure you have reviewed the documentation for
[UI kit](https://docs.civictheme.io/ui-kit) and
[Drupal theme](https://docs.civictheme.io/drupal-theme).


## Build types

There are 2 types of the build that can be run locally (same runs in CI):

1. **Isolated** - installs the theme code on a fresh bare Drupal site using the build scripts from https://github.com/AlexSkrypnyk/drupal_extension_scaffold
2. **Full build** - runs as if the theme code was installed on a `minimnal` or `govcms` profile with all custom modules enabled using the build scripts from https://github.com/drevops/scaffold

## Isolated

The isolated build is used to test the theme code on a fresh Drupal site without
any additional modules or themes. This build is useful to ensure that the theme
code is compatible with the latest Drupal version and does not have any
dependencies on other modules or themes.

To run the isolated build, use the following command:

```bash
cd web/themes/contrib/civictheme
ahoy build
```

This will assemble the full Drupal codebase in `web/themes/contrib/civictheme/build`
directory and will symlink the CivicTheme theme files into the
`web/themes/contrib/civictheme/build/web/themes/custom/civictheme` directory.

**This does not use Docker and does not require a running Docker stack.**

Important: make sure to removed `web/themes/contrib/civictheme/build` directory
before running the `Full build` build to avoid any conflicts.

## Full build (Docker build)

The full build is used to test the theme code on a full Drupal site with all
custom modules and themes enabled. This build is useful to ensure that the theme
code is compatible with the latest Drupal version and the latest GovCMS version
as well as with all custom modules.

The following steps outline the build process:

1. Construct a fresh Drupal 10 site from the GovCMS Drupal profile. Utilize
   `ahoy provision` for a rebuild.
2. Activate additional modules needed for development by installing the
   `civictheme_dev` module.
3. Enable the `civictheme` theme and import its configuration.
4. Generate a `civictheme_demo` sub-theme using the provided scaffolding script
   and set it as the default theme.
5. Activate the `civictheme_admin` module for enhancements in the admin UI.
6. Activate the `civictheme_govcms` module to discard pre-configured GovCMS
   content types.
7. Enable the `civictheme_content` module to incorporate default content into
   the installation.

### Custom Drupal Site Building Scripts

Scripts located in `scripts/custom` execute after the site installation from
the profile, following the order of their numbering.

By default, the site:

- Installs Drupal 10.
- Installs the `govcms` profile.
- Installs the CivicTheme Drupal theme.
- Creates and installs the CivicTheme Demo sub-theme.
- Provides demo content.

Override the default behavior using these environment variables:

- `DRUPAL_PROFILE=minimal` - Uses the `minimal` profile for installation.
  For Drupal 10, this becomes the enforced default.
- `CIVICTHEME_SUBTHEME_ACTIVATION_SKIP=1` - Omits activation of the demo
  sub-theme.
- `CIVICTHEME_GENERATED_CONTENT_CREATE_SKIP=1` - Skips the creation of demo
  content.

These variables can be set prior to the `ahoy provision` command or added
to `.env.local` file to preserve this behavior (run `ahoy up` to apply
without full rebuild).

Example:

```bash
# Install Drupal site using `minimal` profile with CivicTheme.
# Do not create a sub-theme and do not provision demo content.
CIVICTHEME_SUBTHEME_ACTIVATION_SKIP=1 CIVICTHEME_GENERATED_CONTENT_CREATE_SKIP=1 ahoy provision
```

### Compiling theme assets

To compile all assets in all themes: `ahoy fe`

```bash

# CivicTheme Drupal theme
cd web/themes/contrib/civictheme
npm run build

# CivicTheme Drupal Demo theme
cd web/themes/custom/civictheme_demo
npm run build
```

Refer to the [theme's Readme](web/themes/contrib/civictheme/README.md) for more
information about switching of the UIKit version and other theme-specific
details.

### Working with configurations

Configuration is captured using [Config Devel](https://www.drupal.org/project/config_devel) module for:
- CivicTheme theme into `config/install` and `config/optional` directories.
- Development `civictheme_dev` module's `config/install` and `config/optional` directories.

#### Exporting configurations

```bash
ahoy export-config
```

To add new configuration to the export, add configuration name to `civictheme.info.yml`.

Tip: You can get the configuration name by exporting configuration with `drush cex -y`
to `config/default` and using file names without `.yml` extension. Do not forget
to remove all exported configuration files from `config/default` or the next site
install will fail. But this all is already handled in `ahoy export-config`.

Note: Configuration for blocks in `civictheme` will be copied to `civictheme_demo` on
installation of `civictheme_demo`. We do not capture configuration for `civictheme_demo`.

Exclude certain configuration from automatically be added to `civictheme.info.yml`
by adding records to [theme_excluded_configs.txt](./scripts/theme_excluded_configs.txt).
Note that wildcards are supported.

#### Validating configurations

Configuration validation allows to ensure that all configuration is captured
correctly and that there is no conflicting or duplicating configuration was
added to the codebase.

```bash
ahoy lint-config
```

### Working with content profiles

Content profiles are used to capture content for the industry-specific demo
sites. Each content profile is a separate submodule of `civictheme_content`
module that contains content and configuration required to build a demo site.

See main [README.md](README.md#content-profiles) for a list of demo site URLs.

#### Updating content

The workflow to update the content within a content profile consists of 3 steps:

1. Install a site with the desired content profile.
2. Making changes
3. Exporting content and configuration.

These steps are captured below:
```bash
# Step 1: Install a site with the desired content profile.
export CIVICTHEME_CONTENT_PROFILE=default
DRUPAL_PROFILE=minimal CIVICTHEME_SUBTHEME_ACTIVATION_SKIP=1 CIVICTHEME_GENERATED_CONTENT_CREATE_SKIP=1 ahoy provision

# Step 2: Make changes.
# ...

# Step 3: Export content and configuration.
ahoy export-content
```
