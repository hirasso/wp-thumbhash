#!/usr/bin/env bash
# Sync "core" in the wp-env configs with the WordPress version locked in composer.lock
set -e

if ! command -v jq >/dev/null; then
  echo "jq not found, skipping wp-env core sync"
  exit 0
fi

version=$(jq -r '.["packages-dev"][] | select(.name == "roots/wordpress-no-content") | .version' composer.lock)
url="https://wordpress.org/wordpress-$version.zip"

for config in .wp-env.json .wp-env.test.json; do
  if [ "$(jq -r .core "$config")" != "$url" ]; then
    tmp=$(mktemp)
    jq --arg url "$url" '.core = $url' "$config" > "$tmp" && mv "$tmp" "$config"
    echo "Updated core in $config to WordPress $version"
  fi
done
