#!/usr/bin/env bash

# `--include` matches path fragments, so a bare "src" also matches the "src" inside a
# release build in build/. Exclude it explicitly, otherwise every string gets listed a
# second time under a path that only exists on release machines.
./vendor/bin/wp i18n make-pot . languages/wp-thumbhash.pot \
  --include="src,wp-thumbhash.php" \
  --exclude="build,vendor-prefixed" \
  --slug="wp-thumbhash" \
  --headers='{"Report-Msgid-Bugs-To":"https://github.com/hirasso/wp-thumbhash/","POT-Creation-Date":""}'
