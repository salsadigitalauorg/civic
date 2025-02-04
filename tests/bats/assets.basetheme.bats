#!/usr/bin/env bats
#
# Test for base Drupal theme assets.
#
# shellcheck disable=SC2030,SC2031,SC2129

load _helper

@test "Base Drupal theme assets" {
  dir="${DREVOPS_EXPORT_CODE_DIR:-${CUR_DIR}}/web/themes/contrib/civictheme"

  pushd "${dir}" >/dev/null || exit 1

  # Files managed by the CivicTheme Drupal base theme developer.
  assert_dir_exists "assets"
  assert_dir_exists "assets/backgrounds"
  assert_dir_exists "assets/fonts"
  assert_dir_exists "assets/icons"
  assert_dir_exists "assets/logos"
  assert_dir_not_exists "assets/images"
  assert_file_not_exists "assets/images/demo1.jpg"
  assert_file_not_exists "assets/images/demo2.jpg"
  assert_file_not_exists "assets/images/demo3.jpg"
  assert_dir_not_exists "assets/videos"
  assert_file_not_exists "assets/videos/demo.avi"
  assert_file_not_exists "assets/videos/demo.mp4"
  assert_file_not_exists "assets/videos/demo.webm"
  assert_file_not_exists "assets/videos/demo_poster.png"
  assert_file_exists "assets/favicon.ico"

  # Files managed by the CivicTheme Drupal theme CI build.
  assert_dir_exists "dist"

  assert_file_exists "dist/civictheme.css"
  assert_file_not_contains "dist/civictheme.css" "ck-editor__editable"

  assert_file_exists "dist/civictheme.variables.css"
  assert_file_contains "dist/civictheme.variables.css" "--ct"

  assert_file_exists "dist/civictheme.editor.css"
  assert_file_contains "dist/civictheme.editor.css" "ck-editor__editable"

  assert_file_exists "dist/civictheme.layout.css"
  assert_file_contains "dist/civictheme.layout.css" "ct-layout"

  assert_file_exists "dist/civictheme.admin.css"
  assert_file_contains "dist/civictheme.admin.css" "path-admin"

  assert_file_exists "dist/civictheme.js"
  assert_file_not_exists "dist/civictheme-variables.js"
  assert_file_not_exists "dist/civictheme-editor.js"

  assert_dir_exists "dist/assets"
  assert_dir_exists "dist/assets/backgrounds"
  assert_dir_not_exists "dist/assets/fonts"
  assert_dir_exists "dist/assets/icons"
  assert_dir_not_exists "dist/assets/images"
  assert_dir_exists "dist/assets/logos"
  assert_dir_not_exists "dist/assets/videos"
  assert_dir_not_exists "dist/assets/api"

  assert_dir_exists "storybook-static"
  assert_file_exists "storybook-static/index.html"
  assert_file_exists "storybook-static/iframe.html"
  assert_dir_exists "storybook-static/assets"
  assert_dir_exists "storybook-static/assets/backgrounds"
  assert_dir_not_exists "storybook-static/assets/fonts"
  assert_dir_exists "storybook-static/assets/icons"
  assert_dir_not_exists "storybook-static/assets/images"
  assert_dir_exists "storybook-static/assets/logos"
  assert_dir_not_exists "storybook-static/backgrounds"
  assert_dir_not_exists "storybook-static/fonts"
  assert_dir_not_exists "storybook-static/icons"
  assert_dir_not_exists "storybook-static/images"
  assert_dir_not_exists "storybook-static/logos"
  assert_dir_not_exists "storybook-static/videos"
  assert_dir_exists "storybook-static/api"

  popd >/dev/null
}
