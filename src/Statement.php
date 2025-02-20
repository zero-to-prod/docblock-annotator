<?php

namespace Zerotoprod\DocblockAnnotator;

/**
 * Statement types to target.
 *
 * @link https://github.com/zero-to-prod/docblock-annotator
 */
enum Statement: string
{

    /**
     * Indicates that the member type is a method.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case ClassMethod = 'class_method';
    /**
     * Indicates that the member type is a constant.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case Const_ = 'const';
    /**
     * Indicates that the member type is a class.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case Class_ = 'class';
    /**
     * Indicates functions.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case ClassConst = 'class_const';
    /**
     * Indicates that the member type is an enum case.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case EnumCase = 'enum_case';
    /**
     * Indicates that the member type is an enum.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case Enum_ = 'enum';
    /**
     * Indicates functions.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case Function_ = 'function';
    /**
     * Indicates that the member type is a trait.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case Trait_ = 'trait';
    /**
     * Indicates that the member type is a property.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case Property = 'property';
    /**
     * Indicates that the member type is an interface.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    case Interface_ = 'interface';
}