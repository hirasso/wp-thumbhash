<?php

namespace Hirasso\WPThumbhash\WPCLI\Commands;

use Hirasso\WPThumbhash\ImageDownloader;
use Hirasso\WPThumbhash\Plugin;
use Snicco\Component\BetterWPCLI\Command;
use Snicco\Component\BetterWPCLI\Input\Input;
use Snicco\Component\BetterWPCLI\Output\Output;
use Snicco\Component\BetterWPCLI\Style\SniccoStyle;
use Snicco\Component\BetterWPCLI\Synopsis\InputArgument;
use Snicco\Component\BetterWPCLI\Synopsis\InputFlag;
use Snicco\Component\BetterWPCLI\Synopsis\Synopsis;
use WP_Query;
use WP_CLI;

/**
 * A WP CLI command to generate thumbhash placeholders
 * @see https://github.com/snicco/better-wp-cli
 */
class GenerateCommand extends Command
{
    protected static string $name = 'generate';

    protected static string $short_description = 'Generate thumbhash placeholders';

    protected SniccoStyle $io;

    /**
     * Command synopsis.
     */
    public static function synopsis(): Synopsis
    {
        return new Synopsis(
            new InputArgument(
                'ids',
                'Only generate placeholders for these images',
                InputArgument::OPTIONAL | InputArgument::REPEATING
            ),
            new InputFlag(
                'force',
                'Generate placeholders also for images that already have one'
            ),
        );
    }

    public function execute(Input $input, Output $output): int
    {
        $this->io = new SniccoStyle($input, $output);

        $ids = $input->getRepeatingArgument('ids', []);
        $force = $input->getFlag('force');

        if (!$this->validateArgumentIds($ids)) {
            return Command::INVALID;
        }

        $queryArgs = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => Plugin::META_KEY,
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        if ($force) {
            unset($queryArgs['meta_query']);
        }

        $query = new WP_Query($queryArgs);

        ImageDownloader::cleanupOldImages();

        $output->newLine();

        if (!$query->have_posts()) {
            $output->writeln(WP_CLI::colorize("%GSuccess%n No images without placeholders found"));
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($query->posts as $id) {
            $thumbhash = Plugin::generateThumbhash($id);
            $status = WP_CLI::colorize(!!$thumbhash ? "%Ggenerated ✔︎%n" : "%RFailed%n");
            $output->writeln($this->getStatusLine(basename(wp_get_attachment_url($id)), $status));
            if ($thumbhash) {
                $count++;
            }
        }

        $output->newLine();
        $output->writeln(WP_CLI::colorize("%GSuccess%n $count placeholders generated."));

        return Command::SUCCESS;
    }

    /**
     * Create a status line, for example:
     * image.jpg ..................................................... generated ✔︎
     */
    private function getStatusLine(string $start, string $end): string
    {
        $dots = str_repeat('.', max(0, 70 - strlen($start)));
        return "$start $dots $end";
    }

    /**
     * Make sure all ids are numeric
     */
    private function validateArgumentIds(array $ids): bool
    {
        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                $this->io->error("Invalid non-numeric id provided: $id");
                return false;
            }
        }
        return true;
    }
}
