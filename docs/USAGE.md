# Usage

WP Thumbhash generates placeholders on upload so they're ready to go when displaying a page. If you need to generate placeholders for existing images, have
a look at the [WP-CLI commands](#wp-cli-commands).

## Display Placeholders in Your Frontend

Trigger the action `wp-thumbhash/render` for displaying a thumbhash for an image ID:

```php
<figure>
  <?php do_action('wp-thumbhash/render', $id) ?>
  <?php echo wp_get_attachment_image($id, 'large') ?>
</figure>
```

## Styling

```css
figure,
figure img {
  position: relative;
}
figure thumb-hash {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
```

## Render Strategies

wp-thumbhash supports three different render strategies:

- `canvas` (default): Display a canvas with the blurry placeholder image
- `img`: Looks the same as `canvas`, but uses an image with a data URI instead
- `average`: Display a div with the average color of the original image

For example, pass `"average"` as the third argument to display a div with the average color instead:

```php
<figure>
  <?php do_action('wp-thumbhash/render', $id, /* strategy: */ 'average') ?>
  <?php echo wp_get_attachment_image($id, 'large') ?>
</figure>
```

## WP-CLI Commands

If you install wp-thumbhash on an existing site, you can generate thumbhash placeholders for your existing images via [WP-CLI](https://wp-cli.org/):

### Generate Thumbhashes for Existing Images

```shell
wp thumbhash generate
```

This will generate thumbhash placeholders for all your images. To force regeneration even for images that already have a thumbhash, pass a `force` flag:

```shell
wp thumbhash generate --force
```

### Clear Existing Thumbhashes

To clear thumbhash placeholders for all images, run this command:

```shell
wp thumbhash clear
```

To clear thumbhash placeholders only for select images, pass the image ids:

```shell
wp thumbhash clear 50 72 1872
```

### Command Help

Use `wp help <command>` to print the command synopsis to your console:

```shell
wp help thumbhash generate
wp help thumbhash clear
```
