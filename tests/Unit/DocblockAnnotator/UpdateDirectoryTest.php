<?php

namespace Tests\Unit\DocblockAnnotator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;

class UpdateDirectoryTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = vfsStream::setup('root');
    }

    /** @test */
    public function it_can_add_a_comment_to_a_class_using_update_method(): void
    {
        $phpCode = <<<PHP
        <?php
        class Foo {}
        PHP;

        $filePath = vfsStream::url('root/Foo.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateDirectory($this->root->url(), ['comment'], [Annotator::public], [Annotator::class_]);

        $updatedCode = file_get_contents($filePath);
        $expected = <<<PHP
        <?php
        /**
         * comment
         */
        class Foo {}
        PHP;

        $this->assertEquals($expected, $updatedCode);
    }

    /** @test */
    public function it_can_add_comments_to_an_interface_using_update_method(): void
    {
        $phpCode = <<<PHP
        <?php
        interface FooInterface {
            public function doSomething(): void;
        }
        PHP;

        $filePath = vfsStream::url('root/FooInterface.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateDirectory($this->root->url(), ['interface comment'], [Annotator::public], [Annotator::interface_]);

        $updatedCode = file_get_contents($filePath);
        $expected = <<<PHP
        <?php
        /**
         * interface comment
         */
        interface FooInterface {
            public function doSomething(): void;
        }
        PHP;

        $this->assertEquals($expected, $updatedCode);
    }

    /** @test */
    public function it_can_add_comments_to_an_enum_using_update_method(): void
    {
        $phpCode = <<<PHP
        <?php
        enum Suit {
            case Hearts;
            case Diamonds;
        }
        PHP;

        $filePath = vfsStream::url('root/Suit.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateDirectory($this->root->url(), ['enum comment'], [Annotator::public], [Annotator::enum]);

        $updatedCode = file_get_contents($filePath);
        $expected = <<<PHP
        <?php
        /**
         * enum comment
         */
        enum Suit {
            case Hearts;
            case Diamonds;
        }
        PHP;

        $this->assertEquals($expected, $updatedCode);
    }

    /** @test */
    public function it_can_add_comments_to_enum_cases_using_update_method(): void
    {
        $phpCode = <<<PHP
        <?php
        enum Suit {
            case Hearts;
            case Diamonds;
        }
        PHP;

        $filePath = vfsStream::url('root/SuitCases.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateDirectory($this->root->url(), ['enum case comment'], [Annotator::public], [Annotator::enum_case]);

        $updatedCode = file_get_contents($filePath);
        $expected = <<<PHP
        <?php
        enum Suit {
            /**
             * enum case comment
             */
            case Hearts;
            /**
             * enum case comment
             */
            case Diamonds;
        }
        PHP;

        $this->assertEquals($expected, $updatedCode);
    }

    /** @test */
    public function it_can_add_comments_to_constants_using_update_method(): void
    {
        $phpCode = <<<PHP
        <?php
        class Foo {
            public const BAR = 'baz';
        }
        PHP;

        $filePath = vfsStream::url('root/ConstantsTest.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateDirectory($this->root->url(), ['constant comment'], [Annotator::public], [Annotator::constant]);

        $updatedCode = file_get_contents($filePath);
        $expected = <<<PHP
        <?php
        class Foo {
            /**
             * constant comment
             */
            public const BAR = 'baz';
        }
        PHP;

        $this->assertEquals($expected, $updatedCode);
    }

    /** @test */
    public function it_can_add_comments_to_properties_using_update_method(): void
    {
        $phpCode = <<<PHP
        <?php
        class Foo {
            public \$bar = 'baz';
        }
        PHP;

        $filePath = vfsStream::url('root/PropertiesTest.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateDirectory($this->root->url(), ['property comment'], [Annotator::public], [Annotator::property]);

        $updatedCode = file_get_contents($filePath);
        $expected = <<<PHP
        <?php
        class Foo {
            /**
             * property comment
             */
            public \$bar = 'baz';
        }
        PHP;

        $this->assertEquals($expected, $updatedCode);
    }

    /** @test */
    public function it_can_add_comments_to_methods_using_update_method(): void
    {
        $phpCode = <<<PHP
        <?php
        class Foo {
            public function bar() {
                return 'baz';
            }
        }
        PHP;

        $filePath = vfsStream::url('root/MethodsTest.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateDirectory($this->root->url(), ['method comment'], [Annotator::public], [Annotator::method]);

        $updatedCode = file_get_contents($filePath);
        $expected = <<<PHP
        <?php
        class Foo {
            /**
             * method comment
             */
            public function bar() {
                return 'baz';
            }
        }
        PHP;

        $this->assertEquals($expected, $updatedCode);
    }
}