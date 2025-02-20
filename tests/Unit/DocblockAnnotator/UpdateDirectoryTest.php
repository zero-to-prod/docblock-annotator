<?php

namespace Tests\Unit\DocblockAnnotator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Tests\TestCase;
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;
use Zerotoprod\DocblockAnnotator\Statement;
use Zerotoprod\DocblockAnnotator\Modifier;

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
        vfsStream::newFile('Foo.php')
            ->at($this->root)
            ->setContent(<<<PHP
            <?php
            class Foo {}
            PHP);

        $filePath = vfsStream::url('root/Foo.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::Class_]
        );
        $annotator->updateDirectory(['comment'], $this->root->url());

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
        vfsStream::newFile('FooInterface.php')
            ->at($this->root)
            ->setContent(<<<PHP
            <?php
            interface FooInterface {
                public function doSomething(): void;
            }
            PHP);

        $filePath = vfsStream::url('root/FooInterface.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::Interface_]
        );
        $annotator->updateDirectory(['interface comment'], $this->root->url());

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
        vfsStream::newFile('Suit.php')
            ->at($this->root)
            ->setContent(<<<PHP
            <?php
            enum Suit {
                case Hearts;
                case Diamonds;
            }
            PHP);

        $filePath = vfsStream::url('root/Suit.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::Enum_]
        );
        $annotator->updateDirectory(['enum comment'], $this->root->url());

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
        vfsStream::newFile('SuitCases.php')
            ->at($this->root)
            ->setContent(<<<PHP
            <?php
            enum Suit {
                case Hearts;
                case Diamonds;
            }
            PHP);

        $filePath = vfsStream::url('root/SuitCases.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::EnumCase]
        );
        $annotator->updateDirectory(['enum case comment'], $this->root->url());

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
        vfsStream::newFile('ConstantsTest.php')
            ->at($this->root)
            ->setContent(<<<PHP
            <?php
            class Foo {
                public const BAR = 'baz';
            }
            PHP);

        $filePath = vfsStream::url('root/ConstantsTest.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassConst]
        );
        $annotator->updateDirectory(['constant comment'], $this->root->url());

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
        vfsStream::newFile('PropertiesTest.php')
            ->at($this->root)
            ->setContent(<<<PHP
            <?php
            class Foo {
                public \$bar = 'baz';
            }
            PHP);

        $filePath = vfsStream::url('root/PropertiesTest.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::Property]
        );
        $annotator->updateDirectory(['property comment'], $this->root->url());

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
        vfsStream::newFile('MethodsTest.php')
            ->at($this->root)
            ->setContent(<<<PHP
            <?php
            class Foo {
                public function bar() {
                    return 'baz';
                }
            }
            PHP);

        $filePath = vfsStream::url('root/MethodsTest.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod]
        );
        $annotator->updateDirectory(['method comment'], $this->root->url());

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


    /** @test */
    public function it_only_annotates_specified_statements(): void
    {
        vfsStream::newFile('MixedStatements.php')
            ->at($this->root)
            ->setContent(<<<PHP
        <?php
        class Mixed {
            public function method() {}
            public const CONST = 'value';
        }
        PHP);

        $filePath = vfsStream::url('root/MixedStatements.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod]
        );
        $annotator->updateDirectory(['method comment'], $this->root->url());

        $updated = file_get_contents($filePath);
        $expected = <<<PHP
    <?php
    class Mixed {
        /**
         * method comment
         */
        public function method() {}
        public const CONST = 'value';
    }
    PHP;
        $this->assertEquals($expected, $updated);
    }

    /** @test */
    public function it_calls_failure_callback_on_syntax_error(): void
    {
        vfsStream::newFile('ErrorFile.php')
            ->at($this->root)
            ->setContent(<<<PHP
        <?php
        class ErrorClass {
            public function method() {
                return 'error'
            }
        }
        PHP); // Missing semicolon

        vfsStream::url('root/ErrorFile.php');

        $failures = [];
        $failureCallback = static function (string $message) use (&$failures) {
            $failures[] = $message;
        };

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod],
            failure: $failureCallback
        );
        $annotator->updateDirectory(['comment'], $this->root->url());

        $this->assertNotEmpty($failures, 'Failure callback should have been called');
        $this->assertStringContainsString('Syntax error', $failures[0]);
    }

    /** @test */
    public function it_does_not_change_files_with_empty_comments(): void
    {
        vfsStream::newFile('EmptyComments.php')
            ->at($this->root)
            ->setContent(<<<PHP
        <?php
        class EmptyComments {
            public function method() {}
        }
        PHP);

        $filePath = vfsStream::url('root/EmptyComments.php');
        $originalContent = file_get_contents($filePath);

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod]
        );
        $annotator->updateDirectory([], $this->root->url());

        $updated = file_get_contents($filePath);
        $this->assertEquals($originalContent, $updated);
    }
}