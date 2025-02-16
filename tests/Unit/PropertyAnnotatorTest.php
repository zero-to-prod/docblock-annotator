<?php

namespace Tests\Unit;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;

class PropertyAnnotatorTest extends TestCase
{
    /** @test */
    public function adds_a_comment(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
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
                 * comment
                 */
                public string \$property = '';
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