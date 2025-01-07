<?php

namespace Hirasso\WPThumbhash\CLI;

use Snicco\Component\BetterWPCLI\Style\SniccoStyle;

final class InputValidator
{
    public function __construct(
        protected SniccoStyle $io
    ) {}

    public function isNumericArray(array $input, string $message): bool
    {
        $nonNumericValues = array_diff($input, array_filter($input, 'is_numeric'));
        if (! count($nonNumericValues)) {
            return true;
        }
        $this->io->error([
            $message,
            implode(',', array_map(
                fn ($value) => "'".sanitize_text_field($value)."'",
                $nonNumericValues
            )),
        ]);

        return false;
    }
}
