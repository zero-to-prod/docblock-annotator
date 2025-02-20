<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\Statement;

class FunctionTest extends TestCase
{
    /** @test */
    public function adds_comment_to_standalone_function(): void
    {
        $file = <<<PHP
        <?php
        
        function getName()
        {
            return 'John';
        }
        PHP;

        $code = (new Annotator(['Function comment'], ['public'], [Statement::Function_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * Function comment
             */
            function getName()
            {
                return 'John';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function adds_multiple_comments_to_standalone_function(): void
    {
        $file = <<<PHP
        <?php
        
        function getName()
        {
            return 'John';
        }
        PHP;

        $code = (new Annotator(['Comment 1', 'Comment 2'], ['public'], [Statement::Function_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * Comment 1
             * Comment 2
             */
            function getName()
            {
                return 'John';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function updates_existing_standalone_function_comment(): void
    {
        $file = <<<PHP
        <?php
        
        /**
         * Existing comment
         */
        function getName()
        {
            return 'John';
        }
        PHP;

        $code = (new Annotator(['New comment'], ['public'], [Statement::Function_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * Existing comment
             * New comment
             */
            function getName()
            {
                return 'John';
            }
            PHP,
            $code
        );
    }

    /** @test */
    public function ignores_duplicate_standalone_function_comments(): void
    {
        $file = <<<PHP
        <?php
        
        /**
         * @return string
         */
        function getName()
        {
            return 'John';
        }
        PHP;

        $code = (new Annotator(['@return string'], ['public'], [Statement::Function_]))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            
            /**
             * @return string
             */
            function getName()
            {
                return 'John';
            }
            PHP,
            $code
        );
    }
}