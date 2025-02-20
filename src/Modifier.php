<?php

namespace Zerotoprod\DocblockAnnotator;

/**
 * Visibility levels to target
 *
 * @link https://github.com/zero-to-prod/docblock-annotator
 */
enum Modifier: string
{

    /**
     * Indicates that the visibility is private.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case private = 'private';
    /**
     * Indicates that the visibility is public.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case public = 'public';
    /**
     * Indicates that the visibility is protected.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case protected = 'protected';
}