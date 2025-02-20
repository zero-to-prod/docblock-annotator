<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\Statement;

class ClassConstantTest extends TestCase
{
    /** @test */
    public function adds_a_comment(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                public const CONSTANT = 'value';
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
            public const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment1', 'comment2'], ['public'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment1
                 * comment2
                 */
                public const CONSTANT = 'value';
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
            public const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * existing
                 * comment
                 */
                public const CONSTANT = 'value';
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
            public const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * existing
                 * comment
                 */
                public const CONSTANT = 'value';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_public_constant(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                public const CONSTANT = 'value';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function does_not_add_a_comment_public_constant(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::Const_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                public const CONSTANT = 'value';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_private_constant(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            private const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment'], ['private'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                private const CONSTANT = 'value';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_protected_constant(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            protected const CONSTANT = 'value';
        }
        PHP;

        $code = (new Annotator(['comment'], ['protected'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                protected const CONSTANT = 'value';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_to_multiple_constants(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT_1 = 'value1',
                         CONSTANT_2 = 'value2';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                public const CONSTANT_1 = 'value1',
                             CONSTANT_2 = 'value2';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_comments_on_multiple_constants(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            /**
             * existing
             */
            public const CONSTANT_1 = 'value1',
                         CONSTANT_2 = 'value2';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Statement::ClassConst]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * existing
                 * comment
                 */
                public const CONSTANT_1 = 'value1',
                             CONSTANT_2 = 'value2';
            }
            PHP,
            $code
        );
    }
}