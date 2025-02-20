<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\Statement;

class ConstantTest extends TestCase
{
    /** @test */
    public function adds_comment_to_standalone_constant(): void
    {
        $file = <<<PHP
        <?php
        
        const STATUS_ACTIVE = 1;
        PHP;

        $code = (new Annotator(['Constant comment'], ['public'], [Statement::Const_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * Constant comment
             */
            const STATUS_ACTIVE = 1;
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_multiple_comments_to_standalone_constant(): void
    {
        $file = <<<PHP
        <?php
        
        const STATUS_ACTIVE = 1;
        PHP;

        $code = (new Annotator(['Comment 1', 'Comment 2'], ['public'], [Statement::Const_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * Comment 1
             * Comment 2
             */
            const STATUS_ACTIVE = 1;
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_existing_standalone_constant_comment(): void
    {
        $file = <<<PHP
        <?php
        
        /**
         * Existing comment
         */
        const STATUS_ACTIVE = 1;
        PHP;

        $code = (new Annotator(['New comment'], ['public'], [Statement::Const_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * Existing comment
             * New comment
             */
            const STATUS_ACTIVE = 1;
            PHP,
            $code
        );
    }

    /** @test */
    public function ignores_duplicate_standalone_constant_comments(): void
    {
        $file = <<<PHP
        <?php
        
        /**
         * @var int
         */
        const STATUS_ACTIVE = 1;
        PHP;

        $code = (new Annotator(['@var int'], ['public'], [Statement::Const_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * @var int
             */
            const STATUS_ACTIVE = 1;
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_comment_to_multiple_standalone_constants(): void
    {
        $file = <<<PHP
        <?php
        
        const STATUS_ACTIVE = 1, STATUS_INACTIVE = 0;
        PHP;

        $code = (new Annotator(['Status constants'], ['public'], [Statement::Const_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * Status constants
             */
            const STATUS_ACTIVE = 1, STATUS_INACTIVE = 0;
            PHP,
            $code
        );
    }
}