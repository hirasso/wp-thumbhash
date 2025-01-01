# Usage

## Usage

### Markup

```php
<figure>
  <?php if (class_exists('Hirasso\\WPThumbhash\\WPThumbhash')): ?>
    <?= \Hirasso\WPThumbhash\WPThumbhash::render($id) ?>
  <?php endif; ?>
  <?php echo wp_get_attachment_image($id) ?>
</figure>
```

### Styling

```css
figure,
figure img {
  position: relative;
}

figure img {
  display: block;
  width: 100%;
  height: auto;
}

figure thumb-hash {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
```
