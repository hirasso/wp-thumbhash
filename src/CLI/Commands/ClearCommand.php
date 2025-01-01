<?php

namespace Hirasso\WPThumbhash\CLI\Commands;

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
use Snicco\Component\BetterWPCLI\Synopsis\Synopsis;
use WP_Query;

/**
 * Clear thumbhashes from all or select images
 * @see https://github.com/snicco/better-wp-cli
 */
class ClearCommand extends Command
{
    protected static string $name = 'clear';

    protected static string $short_description = 'Clear thumbhashes';

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
        );
    }

    public function execute(Input $input, Output $output): int
    {
        $io = new SniccoStyle($input, $output);

        $ids = $input->getRepeatingArgument('ids', []);

        $io->title("Clearing Thumbhashes");

        $validator = new InputValidator($io);
        if (!$validator->isNumericArray($ids, "Non-numeric ids provided")) {
            return Command::INVALID;
        }

        $queryArgs = WPThumbhash::getQueryArgs(QueryArgsCompare::EXISTS);

        if (!empty($ids)) {
            $queryArgs['post__in'] = array_map('absint', array_map('trim', $ids));
        }

        $query = new WP_Query($queryArgs);

        if (!$query->have_posts()) {
            $io->success("No images with placeholders found");
            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($query->posts as $id) {
            delete_post_meta($id, WPThumbhash::META_KEY);

            $output->writeln(Utils::getStatusLine(
                basename(wp_get_attachment_url($id)),
                $io->colorize('cleared ✔︎', Text::GREEN)
            ));

            $count++;
        }

        $output->newLine();

        $io->success(match ($count) {
            1 => "$count thumbhash cleared",
            0 => "No thumbhashes cleared",
            default => "$count thumbhashes cleared"
        });

        return Command::SUCCESS;
    }
}
