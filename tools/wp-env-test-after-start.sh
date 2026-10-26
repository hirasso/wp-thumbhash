#!/usr/bin/env bash
set -e

# Each `wp-env run` is a separate docker exec round trip, so batch the commands
wp-env --config .wp-env.test.json run cli sh -c "wp theme activate twentytwentyfive \
  && wp rewrite structure '/%postname%/' --hard"
