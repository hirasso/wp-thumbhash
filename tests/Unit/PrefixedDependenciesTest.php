<?php

/**
 * `verify:prefixed` inspects the prefixed files; this exercises them,
 * so a reference Strauss could not rewrite fails here instead of in production.
 */

/** Boot only the prefixed dependencies, without the dev autoloader */
function runWithoutDevAutoloader(string $code): string
{
    $script = sprintf('require "%s/vendor-prefixed/autoload.php"; %s', dirname(__DIR__, 2), $code);

    return (string) shell_exec(sprintf('php -r %s 2>&1', escapeshellarg($script)));
}

test('the prefixed thumbhash encodes an image', function () {
    $output = runWithoutDevAutoloader(<<<'PHP'
        use Hirasso\WPThumbhash\Vendor\Thumbhash\Thumbhash;
        use function Hirasso\WPThumbhash\Vendor\Thumbhash\extract_size_and_pixels_with_gd;
        $image = imagecreatetruecolor(4, 4);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 0, 0));
        ob_start();
        imagepng($image);
        [$width, $height, $pixels] = extract_size_and_pixels_with_gd(ob_get_clean());
        echo Thumbhash::convertHashToString(Thumbhash::RGBAToHash($width, $height, $pixels));
        PHP);

    expect($output)->toMatch('/^[A-Za-z0-9+\/]+$/');
});

test('the unprefixed thumbhash is not registered', function () {
    $output = runWithoutDevAutoloader('var_dump(class_exists(Thumbhash\Thumbhash::class));');

    expect($output)->toContain('bool(false)');
});

test('the bundled plugin-update-checker loads', function () {
    $file = dirname(__DIR__, 2).'/vendor-prefixed/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';

    $output = runWithoutDevAutoloader("require '$file';".<<<'PHP'
        var_dump(class_exists(YahnisElsts\PluginUpdateChecker\v5\PucFactory::class));
        PHP);

    expect($output)->toContain('bool(true)');
});
