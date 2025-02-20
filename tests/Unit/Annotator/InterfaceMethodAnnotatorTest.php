<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\Statement;

class InterfaceMethodAnnotatorTest extends TestCase
{
    /** @test */
    public function adds_a_comment_to_interface_method(): void
    {
        $file = <<<PHP
        <?php
        interface UserInterface
        {
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassMethod]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            interface UserInterface
            {
                /**
                 * comment
                 */
                public function method(): string;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_multiple_comments_to_interface_method(): void
    {
        $file = <<<PHP
        <?php
        interface UserInterface
        {
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['comment1', 'comment2'], ['public'], [Statement::ClassMethod]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            interface UserInterface
            {
                /**
                 * comment1
                 * comment2
                 */
                public function method(): string;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_interface_method_comments(): void
    {
        $file = <<<PHP
        <?php
        interface UserInterface
        {
            /**
             * existing
             */
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassMethod]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            interface UserInterface
            {
                /**
                 * existing
                 * comment
                 */
                public function method(): string;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function ignores_duplicate_comments_on_interface_methods(): void
    {
        $file = <<<PHP
        <?php
        interface UserInterface
        {
            /**
             * @link https://github.com/zero-to-prod/arr
             */
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['@link https://github.com/zero-to-prod/arr'], ['public'], [Statement::ClassMethod]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            interface UserInterface
            {
                /**
                 * @link https://github.com/zero-to-prod/arr
                 */
                public function method(): string;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_inline_comments_on_interface_methods(): void
    {
        $file = <<<PHP
        <?php
        interface UserInterface
        {
            /** existing */
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassMethod]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            interface UserInterface
            {
                /**
                 * existing
                 * comment
                 */
                public function method(): string;
            }
            PHP,
            $code
        );
    }
}