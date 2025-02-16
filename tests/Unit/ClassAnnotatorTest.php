<?php

namespace Tests\Unit;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;

class ClassAnnotatorTest extends TestCase
{
    /** @test */
    public function adds_a_comment_to_class(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::class_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment
             */
            class User
            {
                public function method(): string
                {
                    return '';
                }
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_multiple_comments_to_class(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment1', 'comment2'], ['public'], [Annotator::class_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment1
             * comment2
             */
            class User
            {
                public function method(): string
                {
                    return '';
                }
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_class_comment(): void
    {
        $file = <<<PHP
        <?php
        /**
         * existing
         */
        class User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::class_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * existing
             * comment
             */
            class User
            {
                public function method(): string
                {
                    return '';
                }
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function ignores_duplicate_class_comments(): void
    {
        $file = <<<PHP
        <?php
        /**
         * @link https://github.com/zero-to-prod/arr
         */
        class User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['@link https://github.com/zero-to-prod/arr'], ['public'], [Annotator::class_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * @link https://github.com/zero-to-prod/arr
             */
            class User
            {
                public function method(): string
                {
                    return '';
                }
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_inline_class_comments(): void
    {
        $file = <<<PHP
        <?php
        /** existing */
        class User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::class_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * existing
             * comment
             */
            class User
            {
                public function method(): string
                {
                    return '';
                }
            }
            PHP,
            $code
        );
    }
}