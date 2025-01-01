<?php

namespace Hirasso\WPThumbhash\CLI\Commands;

use Hirasso\WPThumbhash\ImageDownloader;
use Hirasso\WPThumbhash\WPThumbhash;
use Hirasso\WPThumbhash\CLI\InputValidator;
use Hirasso\WPThumbhash\CLI\Utils;
use Hirasso\WPThumbhash\Enums\QueryArgsCompare;
use Snicco\Component\BetterWPCLI\Command;
use Snicco\Component\BetterWPCLI\Input\Input;
use Snicco\Component\BetterWPCLI\Output\Output;
use Snicco\Component\BetterWPCLI\Style\SniccoStyle;
use Snicco\Component\BetterWPCLI\Style\Text;
use Snicco\Component\BetterWPCLI\Synopsis\InputArgument;
use Snicco\Component\BetterWPCLI\Synopsis\InputFlag;
use Snicco\Component\BetterWPCLI\Synopsis\Synopsis;
use WP_Query;

/**
 * @see https://github.com/snicco/better-wp-cli
 */
class GenerateCommand extends Command
{
    protected static string $name = 'generate';

    protected static string $short_description = 'Generate placeholders';

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

    /**
     * Execute the command
     */
    public function execute(Input $input, Output $output): int
    {
        $io = new SniccoStyle($input, $output);

        $ids = $input->getRepeatingArgument('ids', []);
        $force = $input->getFlag('force');

        $io->title(match ($force) {
            true => "Generating Thumbhashes (force: true)",
            default => "Generating Thumbhashes"
        });

        $validator = new InputValidator($io);
        if (!$validator->isNumericArray($ids, "Non-numeric ids provided")) {
            return Command::INVALID;
        }

        $queryArgs = WPThumbhash::getQueryArgs(QueryArgsCompare::NOT_EXISTS);

        if ($force) {
            unset($queryArgs['meta_query']);
        }

        $query = new WP_Query($queryArgs);

        ImageDownloader::cleanupTemporaryFiles();

        if (!$query->have_posts()) {
            $io->success("No images without placeholders found");
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($query->posts as $id) {
            $thumbhash = WPThumbhash::generate($id);
            $status = match (!!$thumbhash) {
                true => $io->colorize('generated ✔︎', Text::GREEN),
                default => $io->colorize('failed ❌', Text::RED)
            };
            $output->writeln(Utils::getStatusLine(basename(wp_get_attachment_url($id)), $status));
            if ($thumbhash) {
                $count++;
            }
        }
        $output->newLine();

        $io->success(match ($count) {
            1 => "$count placeholder generated",
            0 => "No placeholders generated",
            default => "$count placeholders generated"
        });

        return Command::SUCCESS;
    }
}
