<?php

/**
 * PHP CS Fixer Config
 * NOT IN USE ANYMORE – kept here for reference
 *
 * @TODO remove if laravel pint prooves suficcient
 *
 * @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/config.rst
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(['src', 'config']) // src/ and config/ folders
    ->append(glob('*.php')); // toplevel php files

return (new Config)
    ->setRules([
        '@PSR12' => true,
        '@PHP83Migration' => true,
        'no_unused_imports' => true,
        // @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/7906
        'single_space_after_construct' => true,
    ])
    ->setFinder(
        $finder
    );
