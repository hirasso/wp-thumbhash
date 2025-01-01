# Installation

## Via Composer

```shell
composer require hirasso/wp-thumbhash
```

### Update via composer

```shell
# latest version, including major version jumps
composer require hirasso/wp-thumbhash
```

## Via WP CLI

```shell
wp plugin install https://github.com/hirasso/wp-thumbhash/releases/latest/download/wp-thumbhash.zip
```

### Update via WP CLI

```shell
wp plugin update wp-thumbhash
```

## Manually

1. Download the [bundled zip file from the latest release](https://github.com/hirasso/wp-thumbhash/releases/latest/download/wp-thumbhash.zip)
2. In your browser, login to your WP Admin Area and navigate to /wp-admin/plugin-install.php
3. Click "Upload Plugin" and choose `wp-thumbhash.zip` that you just downloaded

### Update Manually

If `DISALLOW_FILE_MODS` is not set to `true`, you can update the plugin directly from the WordPress admin plugins screen, just like any other plugin. To avoid being blocked by GitHub, you can define a `WP_THUMBHASH_GITHUB_TOKEN` in your `wp-config.php` file. Learn more about [creating GitHub Tokens](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens#creating-a-fine-grained-personal-access-token)

```php
// wp-config.php:
define('WP_THUMBHASH_GITHUB_TOKEN', 'qwf_sdgad142...');
```
