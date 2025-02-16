<?php

namespace Zerotoprod\DocblockAnnotator;

use Zerotoprod\DataModel\DataModel;

class Change
{
    use DataModel;

    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const start = 'start';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public int $start;
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const end = 'end';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public int $end;
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const text = 'text';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public string $text;
}