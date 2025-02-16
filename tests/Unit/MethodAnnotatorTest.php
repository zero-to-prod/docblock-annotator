<?php

namespace Tests\Unit;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;

class MethodAnnotatorTest extends TestCase
{
    /** @test */
    public function adds_a_comment(): void
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

        $code = (new Annotator(['comment']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
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
    public function adds_a_comments(): void
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

        $code = (new Annotator(['comment1', 'comment2']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment1
                 * comment2
                 */
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
    public function updates_a_comments(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            /**
             * existing
             */
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * existing
                 * comment
                 */
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
    public function updates_inline_comments(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            /** existing */
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(['comment']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * existing
                 * comment
                 */
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