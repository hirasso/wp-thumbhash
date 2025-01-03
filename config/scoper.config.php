<?php

declare(strict_types=1);

namespace Hirasso\WPThumbhash;

use Exception;
use ZipArchive;

/**
 * php-scoper config for creating a scoped release asset for GitHub Releases
 * This release asset serves as the source of truth for non-composer plugin updates
 * via yahnis-elsts/plugin-update-checker
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 * @see https://github.com/YahnisElsts/plugin-update-checker?tab=readme-ov-file#how-to-release-an-update-1
 */

/** @var Symfony\Component\Finder\Finder $finder */
$finder = \Isolated\Symfony\Component\Finder\Finder::class;

/** exclude global WordPress symbols */
[$wpClasses, $wpFunctions, $wpConstants] = getWpExcludes();

/** Extra files that should make it into the scoped release */
$extraFiles = [...getGitArchiveables()];

/**
 * Return the config for php-scoper
 * @see https://github.com/humbug/php-scoper/blob/main/docs/configuration.md
 */
return [
    'prefix' => __NAMESPACE__ . '\Vendor',
    'exclude-namespaces' => [__NAMESPACE__],
    'php-version' => ComposerJSON::instance()->phpVersion,

    'exclude-classes' => [...$wpClasses, 'WP_CLI'],
    'exclude-functions' => [...$wpFunctions],
    'exclude-constants' => [...$wpConstants, 'WP_CLI', 'true', 'false'],

    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,

    'finders' => [
        $finder::create()->files()->in('src'),
        $finder::create()->files()->in('vendor')->ignoreVCS(true)
            ->notName('/.*\\.sh|composer\\.(json|lock)/')
            ->exclude([
                'sniccowp/php-scoper-wordpress-excludes',
                'bin/'
            ]),
        $finder::create()->append(glob('*.php')),
        $finder::create()->append($extraFiles),
    ],
    'patchers' => [
        /**
         * Remove the prefix from strings in plugin-update-checker/load-v5p5.php
         * @see https://github.com/YahnisElsts/plugin-update-checker/issues/586#issuecomment-2567753162
         */
        static function (string $filePath, string $prefix, string $content): string {
            if (preg_match('/plugin-update-checker\/load-v\d+p\d\.php/', $filePath) === false) {
                return $content;
            }
            return preg_replace('/(["\'])' . preg_quote($prefix) . '\\/', '$1', $content);
        },
    ]
];

/**
 * Read the project's composer.json
 * Usage: ComposerJSON::read()->devDependencies
 */
final readonly class ComposerJSON
{
    /** @var string[] $devDependencies An array of all dev-dependencies' names */
    public array $devDependencies;

    /** @var string $phpVersion e.g. '8.2' */
    public string $phpVersion;

    /** @var string $fullName e.g. 'vendor-name/package-name' */
    public string $fullName;

    /** @var string $vendorName e.g. 'vendor-name' */
    public string $vendorName;

    /** @var string $vendorName e.g. 'package-name' */
    public string $packageName;

    /** @var string $vendorDir e.g. 'vendor' */
    public string $vendorDir;

    public function __construct()
    {
        /** The project root dir, where the composer.json file is */
        $rootDir = dirname(__DIR__);

        $data = json_decode(file_get_contents("$rootDir/composer.json"), true);

        $this->fullName = $data['name'];
        [$this->vendorName, $this->packageName] = explode('/', $this->fullName);

        $this->devDependencies = array_keys($data['require-dev'] ?? []);

        preg_match('/\d+\.\d+/', $data['require']['php'], $matches);
        $this->phpVersion = $matches[0];

        $this->vendorDir = trim($data['config']['vendor-dir'] ?? 'vendor', '/');
    }

    /**
     * @return ComposerJSON The singleton instance
     */
    public static function instance()
    {
        static $instance;

        $instance ??= new static();

        return $instance;
    }
}

/**
 * Read WordPress excludes from sniccowp/php-scoper-wordpress-excludes
 * @see https://github.com/humbug/php-scoper/blob/main/docs/further-reading.md#wordpress-support
 */
function getWpExcludes(): array
{
    $baseDir = dirname(__DIR__) . '/vendor/sniccowp/php-scoper-wordpress-excludes/generated';

    $excludes = [];

    foreach (['classes', 'functions', 'constants'] as $type) {
        $excludes[] = json_decode(
            file_get_contents("$baseDir/exclude-wordpress-$type.json"),
            true,
        );
    }

    return $excludes;
}

/**
 * Get all top-level <git archive>-able files and folders.
 */
function getGitArchiveables(bool $includeDirs = false): array
{
    $entries = [];
    $name = ComposerJSON::instance()->vendorName . '-' . ComposerJSON::instance()->packageName;
    $zipFile = "/tmp/$name.zip";

    exec("git archive --format=zip --output=$zipFile HEAD");

    $zip = new ZipArchive();

    if ($zip->open($zipFile) !== true) {
        throw new Exception("Failed to open ZIP archive: $zipFile");
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);

        $isToplevelFile = !str_contains($entry, '/');
        $isToplevelDir = str_ends_with($entry, '/');

        if ($isToplevelDir && !$includeDirs) {
            continue;
        }
        if (!$isToplevelFile) {
            continue;
        }

        $entries[] = $entry;
    }

    $zip->close();

    exec("rm -rf $zipFile");

    return $entries;
}