<?php

namespace Hirasso\WPThumbhash;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Check for Updates using Plugin Update Checker
 * @see https://github.com/YahnisElsts/plugin-update-checker
 */
class UpdateChecker
{
    public static function init(string $entryPoint)
    {
        /** get owner and name from the composer.json */
        $composerJSON = json_decode(file_get_contents(WP_THUMBHASH_PLUGIN_DIR . "/composer.json"));
        [$vendor, $slug] = explode("/", $composerJSON->name);

        /** build the update checker */
        $checker = PucFactory::buildUpdateChecker(
            "https://github.com/$vendor/$slug/",
            $entryPoint,
            $slug,
        );

        $checker->setBranch('main');

        if ($token = static::getGitHubToken()) {
            $checker->setAuthentication($token);
        }

        /**
         * Expect a "$slug.zip" attached to every release
         * @var \YahnisElsts\PluginUpdateChecker\v5p5\Vcs\GitHubApi $api
         */
        $api = $checker->getVcsApi();
        $api->enableReleaseAssets("/$slug\.zip/i", $api::REQUIRE_RELEASE_ASSETS);

        $checker->addFilter('vcs_update_detection_strategies', [static::class, 'update_strategies'], 999);
    }

    /**
     * Get the WP_THUMBHASH_GITHUB_TOKEN for authenticated GitHub requests
     */
    private static function getGitHubToken(): ?string
    {
        if (
            defined('WP_THUMBHASH_GITHUB_TOKEN')
            && is_string(WP_THUMBHASH_GITHUB_TOKEN)
            && !empty(trim(WP_THUMBHASH_GITHUB_TOKEN))
        ) {
            return WP_THUMBHASH_GITHUB_TOKEN;
        }
        return null;
    }

    /**
     * Only keep the "latest_release" strategy
     */
    public static function update_strategies(array $strategies): array
    {
        return ['latest_release' => $strategies['latest_release']];
    }
}
