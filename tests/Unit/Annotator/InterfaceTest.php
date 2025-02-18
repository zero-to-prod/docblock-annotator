<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;

class InterfaceTest extends TestCase
{
    /** @test */
    public function adds_a_comment_to_interface(): void
    {
        $file = <<<PHP
        <?php
        interface UserInterface
        {
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::interface_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment
             */
            interface UserInterface
            {
                public function method(): string;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_multiple_comments_to_interface(): void
    {
        $file = <<<PHP
        <?php
        interface UserInterface
        {
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['comment1', 'comment2'], ['public'], [Annotator::interface_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * comment1
             * comment2
             */
            interface UserInterface
            {
                public function method(): string;
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_interface_comment(): void
    {
        $file = <<<PHP
        <?php
        /**
         * existing
         */
        interface UserInterface
        {
            public function method(): string;
        }
        PHP;

        $code = (new Annotator(['comment'], ['public'], [Annotator::interface_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            /**
             * existing
             * comment
             */
            interface UserInterface
            {
                public function method(): string;
            }
            PHP,
            $code
        );
    }
}