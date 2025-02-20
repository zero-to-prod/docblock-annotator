<?php

namespace Tests\Unit\Annotator;

use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\Statement;

class MemberTest extends TestCase
{

    /** @test */
    public function only_annotates_methods(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT = 'value';
            
            public \$property = '';
            
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(
            ['comment'],
            ['public'],
            [Statement::ClassMethod]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                public const CONSTANT = 'value';
                
                public \$property = '';
                
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
    public function only_annotates_properties(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT = 'value';
            
            public \$property = '';
            
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(
            ['comment'],
            ['public'],
            [Statement::Property]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                public const CONSTANT = 'value';
                
                /**
                 * comment
                 */
                public \$property = '';
                
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
    public function only_annotates_constants(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT = 'value';
            
            public \$property = '';
            
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(
            ['comment'],
            ['public'],
            [Statement::ClassConst]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                /**
                 * comment
                 */
                public const CONSTANT = 'value';
                
                public \$property = '';
                
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
    public function annotates_multiple_member_types(): void
    {
        $file = <<<PHP
        <?php
        class User
        {
            public const CONSTANT = 'value';
            
            public \$property = '';
            
            public function method(): string
            {
                return '';
            }
        }
        PHP;

        $code = (new Annotator(
            ['comment'],
            ['public'],
            [Statement::ClassMethod, Statement::Property, Statement::Property]
        ))->process($file);

        self::assertEquals(
            <<<PHP
            <?php
            class User
            {
                public const CONSTANT = 'value';
                
                /**
                 * comment
                 */
                public \$property = '';
                
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
}