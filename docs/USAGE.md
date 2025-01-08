# Usage

## Display Placeholders in Your Frontend

```php
<figure>
  <?php do_action('wp-thumbhash', $id) ?>
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

## WP-CLI Commands

Thumbhashes are generated on upload so they're ready to go when displaying a page. If you install wp-thumbhash on an existing site, you can generate thumbhashes for your existing images via [WP-CLI](https://wp-cli.org/):

### Generate Thumbhashes for Existing Images

```shell
wp thumbhash generate
```

This will generate thumbhashes for all your images. To force regeneration even for images that already have a thumbhash, pass a `force` flag:

```shell
wp thumbhash generate --force
```

### Clear Existing Thumbhashes

To clear thumbhashes for all images, run this command:

```shell
wp thumbhash clear
```

To clear thumbhashes only for select images, pass the image ids:

```shell
wp thumbhash clear 50 72 1872
```

### Command Help

Use `wp help <command>` to print the command synopsis to your console:

```shell
wp help thumbhash generate
wp help thumbhash clear
```