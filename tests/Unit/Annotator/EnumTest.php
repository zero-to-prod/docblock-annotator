<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;

class EnumTest extends TestCase
{
    /** @test */
    public function adds_a_comment_to_enum(): void
    {
        $file = <<<PHP
        <?php
        enum Suit
        {
            case Hearts;
            case Diamonds;
        }
        PHP;

        // We only annotate 'enum', ignoring cases.
        $code = (new Annotator(
            ['comment'],
            [Annotator::public],  // Visibility includes 'public'
            [Annotator::enum]     // Only want to annotate the enum declaration
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment
             */
            enum Suit
            {
                case Hearts;
                case Diamonds;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_comment_for_enum(): void
    {
        $file = <<<PHP
        <?php
        /**
         * existing
         */
        enum Suit
        {
            case Hearts;
        }
        PHP;

        $code = (new Annotator(
            ['newComment'],
            [Annotator::public],
            [Annotator::enum]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * existing
             * newComment
             */
            enum Suit
            {
                case Hearts;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_comment_to_enum_cases(): void
    {
        $file = <<<PHP
        <?php
        enum Suit
        {
            case Hearts;
            case Diamonds;
        }
        PHP;

        // Annotate only enum cases (not the enum itself)
        $code = (new Annotator(
            ['comment'],
            [Annotator::public],
            [Annotator::enum_case]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            enum Suit
            {
                /**
                 * comment
                 */
                case Hearts;
                /**
                 * comment
                 */
                case Diamonds;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_comment_on_enum_cases(): void
    {
        $file = <<<PHP
        <?php
        enum Suit
        {
            /**
             * existing
             */
            case Hearts;
        }
        PHP;

        $code = (new Annotator(
            ['newComment'],
            [Annotator::public],
            [Annotator::enum_case]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            enum Suit
            {
                /**
                 * existing
                 * newComment
                 */
                case Hearts;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function ignores_duplicate_comments_on_enum(): void
    {
        $file = <<<PHP
        <?php
        /**
         * @link https://github.com/zero-to-prod/example
         */
        enum Suit
        {
            case Hearts;
        }
        PHP;

        // Same comment as existing
        $code = (new Annotator(
            ['@link https://github.com/zero-to-prod/example'],
            [Annotator::public],
            [Annotator::enum]
        ))->process($file);

        // Should remain unchanged
        self::assertEquals(
            <<<PHP
            <?php
            /**
             * @link https://github.com/zero-to-prod/example
             */
            enum Suit
            {
                case Hearts;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function ignores_duplicate_comments_on_enum_case(): void
    {
        $file = <<<PHP
        <?php
        enum Suit
        {
            /**
             * existing
             */
            case Hearts;
        }
        PHP;

        $code = (new Annotator(
            ['existing'],  // This line is already there
            [Annotator::public],
            [Annotator::enum_case]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            enum Suit
            {
                /**
                 * existing
                 */
                case Hearts;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function annotates_both_enum_and_enum_cases(): void
    {
        $file = <<<PHP
        <?php
        enum Suit
        {
            case Hearts;
            case Diamonds;
        }
        PHP;

        // We annotate both the enum and the enum cases
        $code = (new Annotator(
            ['comment'],
            [Annotator::public],
            [Annotator::enum, Annotator::enum_case]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment
             */
            enum Suit
            {
                /**
                 * comment
                 */
                case Hearts;
                /**
                 * comment
                 */
                case Diamonds;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function does_not_annotate_enum_or_cases_if_not_configured(): void
    {
        $file = <<<PHP
        <?php
        enum Suit
        {
            case Hearts;
            case Diamonds;
        }
        PHP;

        // We don't list enum or enum_case in $members
        $code = (new Annotator(
            ['comment'],
            [Annotator::public],
            [Annotator::class_] // only 'class' - not enum
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            enum Suit
            {
                case Hearts;
                case Diamonds;
            }
            PHP,
            $code
        );
    }

    /**
     * You could also test how 'private' or 'protected' visibility
     * interacts with enum cases. By default, they behave as if "public."
     */
    /** @test */
    public function does_not_annotate_enum_case_if_visibility_not_included(): void
    {
        $file = <<<PHP
        <?php
        enum Suit
        {
            case Hearts;
        }
        PHP;

        $code = (new Annotator(
            ['comment'],
            [Annotator::private],
            [Annotator::enum_case]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            enum Suit
            {
                case Hearts;
            }
            PHP,
            $code
        );
    }
}