<?php

namespace Tests\Unit\DocblockAnnotator;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;
use Zerotoprod\DocblockAnnotator\Modifier;
use Zerotoprod\DocblockAnnotator\Statement;

class UpdateFilesTest extends TestCase
{
    private $root;

    protected function setUp(): void
    {
        // Set up the virtual filesystem before each test
        $this->root = vfsStream::setup('root');
    }

    /** @test */
    public function it_can_annotate_multiple_files(): void
    {
        // Create files within the virtual filesystem
        vfsStream::newFile('File1.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
            <?php
            class First {
                public function foo() {
                    return 'foo';
                }
            }
            PHP
            );

        vfsStream::newFile('File2.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
            <?php
            class Second {
                public \$bar = 'bar';
            }
            PHP
            );

        $filePath1 = vfsStream::url('root/File1.php');
        $filePath2 = vfsStream::url('root/File2.php');

        $filesUpdated = [];
        $successCallback = static function (string $file) use (&$filesUpdated) {
            $filesUpdated[] = $file;
        };

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod, Statement::Property],
            success: $successCallback
        );

        $annotator->updateFiles(
            ['Generic docblock line'],
            [$filePath1, $filePath2]
        );

        $updated1 = file_get_contents($filePath1);
        $expected1 = <<<PHP
        <?php
        class First {
            /**
             * Generic docblock line
             */
            public function foo() {
                return 'foo';
            }
        }
        PHP;
        $this->assertEquals($expected1, $updated1);

        $updated2 = file_get_contents($filePath2);
        $expected2 = <<<PHP
        <?php
        class Second {
            /**
             * Generic docblock line
             */
            public \$bar = 'bar';
        }
        PHP;
        $this->assertEquals($expected2, $updated2);

        $this->assertCount(2, $filesUpdated, 'Two files should have been updated');
        $this->assertContains($filePath1, $filesUpdated);
        $this->assertContains($filePath2, $filesUpdated);
    }

    /** @test */
    public function it_calls_success_callback_only_for_changed_files(): void
    {
        // Create files within the virtual filesystem
        vfsStream::newFile('File1.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
            <?php
            class One {
                public function alpha() {
                    return 'alpha';
                }
            }
            PHP
            );

        vfsStream::newFile('File2.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
            <?php
            class Two {
                /**
                 * existing doc
                 */
                public function beta() {
                    return 'beta';
                }
            }
            PHP
            );

        $filePath1 = vfsStream::url('root/File1.php');
        $filePath2 = vfsStream::url('root/File2.php');

        $filesThatChanged = [];
        $successCallback = static function (string $file) use (&$filesThatChanged) {
            $filesThatChanged[] = $file;
        };

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod],
            success: $successCallback
        );

        $annotator->updateFiles(
            ['existing doc'],
            [$filePath1, $filePath2]
        );

        $updated1 = file_get_contents($filePath1);
        $expected1 = <<<PHP
        <?php
        class One {
            /**
             * existing doc
             */
            public function alpha() {
                return 'alpha';
            }
        }
        PHP;
        $this->assertEquals($expected1, $updated1);

        $updated2 = file_get_contents($filePath2);
        $this->assertStringContainsString('existing doc', $updated2);
        $this->assertEquals(
            <<<PHP
        <?php
        class Two {
            /**
             * existing doc
             */
            public function beta() {
                return 'beta';
            }
        }
        PHP,
            $updated2,
            'File2 was not updated (no changes)'
        );

        $this->assertCount(1, $filesThatChanged);
        $this->assertEquals($filePath1, $filesThatChanged[0]);
    }

    /** @test */
    public function it_updates_files_with_different_existing_docblocks(): void
    {
        vfsStream::newFile('File.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
        <?php
        class Test {
            /**
             * Old docblock
             */
            public function method() {
                return 'method';
            }
        }
        PHP
            );

        $filePath = vfsStream::url('root/File.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod]
        );

        $annotator->updateFiles(
            ['New docblock line'],
            [$filePath]
        );

        $updated = file_get_contents($filePath);
        $expected = <<<PHP
    <?php
    class Test {
        /**
         * Old docblock
         * New docblock line
         */
        public function method() {
            return 'method';
        }
    }
    PHP;
        $this->assertEquals($expected, $updated);
    }

    /** @test */
    public function it_annotates_multiple_statements_in_a_file(): void
    {
        vfsStream::newFile('File.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
        <?php
        class Test {
            public function first() {
                return 'first';
            }
            public function second() {
                return 'second';
            }
        }
        PHP
            );

        $filePath = vfsStream::url('root/File.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod]
        );

        $annotator->updateFiles(
            ['Generic docblock'],
            [$filePath]
        );

        $updated = file_get_contents($filePath);
        $expected = <<<PHP
    <?php
    class Test {
        /**
         * Generic docblock
         */
        public function first() {
            return 'first';
        }
        /**
         * Generic docblock
         */
        public function second() {
            return 'second';
        }
    }
    PHP;
        $this->assertEquals($expected, $updated);
    }

    /** @test */
    public function it_only_annotates_specified_modifiers(): void
    {
        vfsStream::newFile('File.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
        <?php
        class Test {
            public function publicMethod() {
                return 'public';
            }
            private function privateMethod() {
                return 'private';
            }
        }
        PHP
            );

        $filePath = vfsStream::url('root/File.php');

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod]
        );

        $annotator->updateFiles(
            ['Docblock for public methods'],
            [$filePath]
        );

        $updated = file_get_contents($filePath);
        $expected = <<<PHP
    <?php
    class Test {
        /**
         * Docblock for public methods
         */
        public function publicMethod() {
            return 'public';
        }
        private function privateMethod() {
            return 'private';
        }
    }
    PHP;
        $this->assertEquals($expected, $updated);
    }

    /** @test */
    public function it_annotates_global_functions(): void
    {
        vfsStream::newFile('File.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
        <?php
        function globalFunction() {
            return 'global';
        }
        PHP
            );

        $filePath = vfsStream::url('root/File.php');

        $annotator = new DocblockAnnotator(
            statements: [Statement::Function_]
        );

        $annotator->updateFiles(
            ['Docblock for functions'],
            [$filePath]
        );

        $updated = file_get_contents($filePath);
        $expected = <<<PHP
    <?php
    /**
     * Docblock for functions
     */
    function globalFunction() {
        return 'global';
    }
    PHP;
        $this->assertEquals($expected, $updated);
    }

    /** @test */
    public function it_annotates_classes(): void
    {
        vfsStream::newFile('File.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
        <?php
        class TestClass {
            // Some code
        }
        PHP
            );

        $filePath = vfsStream::url('root/File.php');

        $annotator = new DocblockAnnotator(
            statements: [Statement::Class_]
        );

        $annotator->updateFiles(
            ['Docblock for classes'],
            [$filePath]
        );

        $updated = file_get_contents($filePath);
        $expected = <<<PHP
    <?php
    /**
     * Docblock for classes
     */
    class TestClass {
        // Some code
    }
    PHP;
        $this->assertEquals($expected, $updated);
    }

    /** @test */
    public function it_calls_failure_callback_on_syntax_error(): void
    {
        vfsStream::newFile('File.php')
            ->at($this->root)
            ->setContent(
                <<<PHP
        <?php
        class Test {
            public function method() {
                return 'method'
            }
        }
        PHP
            ); // Missing semicolon

        $filePath = vfsStream::url('root/File.php');

        $failures = [];
        $failureCallback = static function ($message) use (&$failures) {
            $failures[] = $message;
        };

        $annotator = new DocblockAnnotator(
            modifiers: [Modifier::public],
            statements: [Statement::ClassMethod],
            failure: $failureCallback
        );

        $annotator->updateFiles(
            ['Docblock'],
            [$filePath]
        );

        $this->assertNotEmpty($failures, 'Failure callback should have been called');
        $this->assertStringContainsString('Syntax error', $failures[0]);
    }

    /** @test */
    public function it_handles_non_php_files_gracefully(): void
    {
        vfsStream::newFile('File.txt')
            ->at($this->root)
            ->setContent('Just some text');

        $filePath = vfsStream::url('root/File.txt');

        $failures = [];
        $failureCallback = static function (string $message) use (&$failures) {
            $failures[] = $message;
        };

        $annotator = new DocblockAnnotator(
            failure: $failureCallback
        );

        $annotator->updateFiles(
            ['Docblock'],
            [$filePath]
        );

        $this->assertEmpty($failures, 'Failure callback should not have been called for non-PHP file');
    }
}