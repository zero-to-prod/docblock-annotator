<?php

namespace Tests\Unit;

use Tests\TestCase;
use Zerotoprod\DataModel\DataModel;
use Zerotoprod\DocblockAnnotator\Annotator;

class MixedTypesTest extends TestCase
{
    /** @test */
    public function adds_a_comment(): void
    {
        $file = <<<PHP
        <?php
        class Change
        {
            use DataModel;
        
            public const start = 'start';
            public int \$start;
            public const end = 'end';
            public int \$end;
            public const text = 'text';
            public string \$text;
        }
        PHP;

        $code = (new Annotator(['Comment', 'test']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class Change
            {
                use DataModel;

                /**
                 * Comment
                 * test
                 */
                public const start = 'start';
                /**
                 * Comment
                 * test
                 */
                public int \$start;
                /**
                 * Comment
                 * test
                 */
                public const end = 'end';
                /**
                 * Comment
                 * test
                 */
                public int \$end;
                /**
                 * Comment
                 * test
                 */
                public const text = 'text';
                /**
                 * Comment
                 * test
                 */
                public string \$text;
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
            public string \$property = '';
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
                public string \$property = '';
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
            public string \$property = '';
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
                public string \$property = '';
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
            public string \$property = '';
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
                public string \$property = '';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_public_property(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public string \$property = '';
        }
        PHP;

        $code = (new Annotator(['comment'], ['public']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                public string \$property = '';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function does_not_add_a_comment_public_property(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public string \$property = '';
        }
        PHP;

        $code = (new Annotator(['comment'], ['private']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                public string \$property = '';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_private_property(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            private string \$property = '';
        }
        PHP;

        $code = (new Annotator(['comment'], ['private']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                private string \$property = '';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_protected_property(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            protected string \$property = '';
        }
        PHP;

        $code = (new Annotator(['comment'], ['protected']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                protected string \$property = '';
            }
            PHP,
            $code
        );
    }
}