<?php

namespace Hirasso\WPThumbhash;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
use YahnisElsts\PluginUpdateChecker\v5p7\PucFactory as VersionedPucFactory;
use YahnisElsts\PluginUpdateChecker\v5p7\Vcs\GitHubApi;

/**
 * Check for Updates using Plugin Update Checker
 *
 * @see https://github.com/YahnisElsts/plugin-update-checker
 */
class UpdateChecker
{
    public static function init(string $entryPoint): void
    {
        self::loadLibrary();

        /** get vendorName and name from the composer.json */
        $composerJSON = json_decode(file_get_contents(baseDir().'/composer.json'));
        [$vendor, $slug] = explode('/', $composerJSON->name);

        /** build the update checker */
        $checker = PucFactory::buildUpdateChecker(
            "https://github.com/$vendor/$slug/",
            $entryPoint,
            $slug,
        );

        $checker->setBranch('main');

        if ($token = self::getGitHubToken()) {
            $checker->setAuthentication($token);
        }

        /**
         * Expect a "$slug.zip" attached to every release
         *
         * @var GitHubApi $api
         */
        $api = $checker->getVcsApi();
        $api->enableReleaseAssets("/$slug\.zip/i", $api::REQUIRE_RELEASE_ASSETS);

        $checker->addFilter('vcs_update_detection_strategies', [self::class, 'update_strategies'], 999);
    }

    /**
     * Load the bundled, unprefixed copy of plugin-update-checker.
     * A second copy is safe to load; skipping it just avoids a redundant autoloader.
     */
    private static function loadLibrary(): void
    {
        if (class_exists(VersionedPucFactory::class, false)) {
            return;
        }

        require_once baseDir().'/vendor-prefixed/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
    }

    /**
     * Get the WP_THUMBHASH_GITHUB_TOKEN for authenticated GitHub requests
     */
    private static function getGitHubToken(): ?string
    {
        return defined('WP_THUMBHASH_GITHUB_TOKEN') ? WP_THUMBHASH_GITHUB_TOKEN : null;
    }

    /**
     * Only keep the "latest_release" strategy
     *
     * @param  array<string, mixed>  $strategies
     * @return array<string, mixed>
     */
    public static function update_strategies(array $strategies): array
    {
        return ['latest_release' => $strategies['latest_release']];
    }
}
