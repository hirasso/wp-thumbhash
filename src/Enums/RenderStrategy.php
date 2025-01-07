<?php

namespace Hirasso\WPThumbhash\Enums;

enum RenderStrategy: string
{
    case CANVAS = 'canvas';
    case IMG = 'img';
    case AVERAGE = 'average';
}
