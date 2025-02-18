<?php

namespace Tests\Unit;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;

class DocblockAnnotatorUpdateFilesTest extends TestCase
{

    /** @test */
    public function it_can_annotate_multiple_files(): void
    {
        $filePath1 = vfsStream::url('root/File1.php');
        $filePath2 = vfsStream::url('root/File2.php');

        $phpCode1 = <<<PHP
        <?php
        class First {
            public function foo() {
                return 'foo';
            }
        }
        PHP;
        file_put_contents($filePath1, $phpCode1);

        $phpCode2 = <<<PHP
        <?php
        class Second {
            public \$bar = 'bar';
        }
        PHP;
        file_put_contents($filePath2, $phpCode2);

        $filesUpdated = [];
        $successCallback = static function (string $file) use (&$filesUpdated) {
            $filesUpdated[] = $file;
        };

        DocblockAnnotator::updateFiles(
            [$filePath1, $filePath2],
            ['Generic docblock line'],
            [Annotator::public],
            [Annotator::method, Annotator::property],
            $successCallback
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
        $filePath1 = vfsStream::url('root/File1.php');
        $filePath2 = vfsStream::url('root/File2.php');

        $phpCode1 = <<<PHP
        <?php
        class One {
            public function alpha() {
                return 'alpha';
            }
        }
        PHP;
        file_put_contents($filePath1, $phpCode1);

        $phpCode2 = <<<PHP
        <?php
        class Two {
            /**
             * existing doc
             */
            public function beta() {
                return 'beta';
            }
        }
        PHP;
        file_put_contents($filePath2, $phpCode2);

        $filesThatChanged = [];
        $successCallback = static function (string $file) use (&$filesThatChanged) {
            $filesThatChanged[] = $file;
        };

        DocblockAnnotator::updateFiles(
            [$filePath1, $filePath2],
            ['existing doc'],
            [Annotator::public],
            [Annotator::method],
            $successCallback
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
        $this->assertEquals($phpCode2, $updated2, 'File2 was not updated (no changes)');

        $this->assertCount(1, $filesThatChanged);
        $this->assertEquals($filePath1, $filesThatChanged[0]);
    }
}