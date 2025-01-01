<?php

namespace Hirasso\WPThumbhash\Enums;

enum ImageDriver: string
{
    case IMAGICK = 'imagick';
    case GD = 'gd';
}
