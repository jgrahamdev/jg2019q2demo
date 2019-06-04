#!/usr/bin/env bash
# Temporary hack around the fact that symlinked packages don't actually run dependency calculation. All of these packages
# should match the require-dev packages in composer.json.
ORCA_BUILD_ROOT="$(cd "../orca-build" && pwd)"
composer -d${ORCA_BUILD_ROOT} require "drupal/entity_reference_revisions:1.4" "drupal/paragraphs:1.2" "drupal/field_permissions:^1.0@beta" "drupal/entity_embed:1.0.0-beta2" "drupal/entity:^1.0.0-beta1" "drupal/entity_browser:2.0-alpha2" "drupal/file_entity:^2.0@beta"
