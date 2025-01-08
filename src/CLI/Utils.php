<?php

namespace Hirasso\WPThumbhash\CLI;

class Utils
{
    /**
     * Create a status line, for example:
     * image.jpg ..................................................... generated ✔︎
     */
    public static function getStatusLine(string $start, string $end): string
    {
        [$start, $end] = [
            static::truncateMiddle($start),
            static::truncateMiddle($end),
        ];
        $dotsAmount = max(0, static::getTerminalWidth() - strlen($start) - strlen($end));
        $dots = str_repeat('.', $dotsAmount);

        return sanitize_text_field("$start $dots $end");
    }

    /**
     * Get the curent terminal width
     */
    private static function getTerminalWidth(): int
    {
        // Redirect stderr to /dev/null to handle potential errors gracefully
        if ($output = exec('stty size 2>/dev/null')) {
            [$rows, $cols] = explode(' ', $output);

            return (int) $cols;
        }

        // Fallback to a default width if the command fails
        return 80;
    }

    /**
     * Trunkate a string in the middle if it's too long
     */
    private static function truncateMiddle(string $string, int $maxLength = 40, string $placeholder = '...')
    {
        $strLength = strlen($string);

        // Return the original string if it's shorter than or equal to the max length
        if ($strLength <= $maxLength) {
            return $string;
        }

        $placeholderLength = strlen($placeholder);
        $keepLength = ($maxLength - $placeholderLength) / 2;

        // Split the string into the parts to keep at the start and end
        $start = substr($string, 0, floor($keepLength));
        $end = substr($string, -ceil($keepLength));

        return $start.$placeholder.$end;
    }
}
