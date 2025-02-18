<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;

class TraitTest extends TestCase
{
    /** @test */
    public function adds_a_comment_to_class(): void
    {
        $file = <<<PHP
        <?php
        trait User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::trait_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment
             */
            trait User
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
        trait User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment1', 'comment2'], ['public'], [Annotator::trait_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment1
             * comment2
             */
            trait User
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
    public function updates_trait_comment(): void
    {
        $file = <<<PHP
        <?php
        /**
         * existing
         */
        trait User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::trait_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * existing
             * comment
             */
            trait User
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
    public function ignores_duplicate_trait_comments(): void
    {
        $file = <<<PHP
        <?php
        /**
         * @link https://github.com/zero-to-prod/arr
         */
        trait User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['@link https://github.com/zero-to-prod/arr'], ['public'], [Annotator::trait_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * @link https://github.com/zero-to-prod/arr
             */
            trait User
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
    public function updates_inline_trait_comments(): void
    {
        $file = <<<PHP
        <?php
        /** existing */
        trait User
        {
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::trait_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * existing
             * comment
             */
            trait User
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