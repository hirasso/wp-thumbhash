<?php

namespace Hirasso\WPThumbhash;

/**
 * The normalized hash object, for use in the frontend
 */
final readonly class Hash
{
    public function __construct(
        public string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
