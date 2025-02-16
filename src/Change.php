<?php

namespace Zerotoprod\DocblockAnnotator;

use Zerotoprod\DataModel\DataModel;

class Change
{
    use DataModel;

    public const start = 'start';
    public int $start;
    public const end = 'end';
    public int $end;
    public const text = 'text';
    public string $text;
}