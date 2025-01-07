---
"wp-thumbhash": patch
---

Throw an exception if WP_DEBUG is true and the strategy in`do_action('wp-thumbhash/render', $id, $strategy)` doesn't exist
