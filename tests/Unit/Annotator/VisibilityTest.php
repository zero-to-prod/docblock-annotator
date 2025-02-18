<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;

class VisibilityTest extends TestCase
{

    /** @test */
    public function adds_a_comment_public_member(): void
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

        $code = (new Annotator(['comment'], ['public']))->process($file);

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
    public function does_not_add_a_comment_public_member(): void
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

        $code = (new Annotator(['comment'], ['private']))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
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
    public function adds_a_comment_private_member(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            private function method(): string
            {
                return '';
            }
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
                private function method(): string
                {
                    return '';
                }
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_a_comment_protected_member(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            protected function method(): string
            {
                return '';
            }
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
                protected function method(): string
                {
                    return '';
                }
            }
            PHP,
            $code
        );
    }
}