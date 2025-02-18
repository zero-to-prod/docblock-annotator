<?php

namespace Tests\Unit;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Zerotoprod\DocblockAnnotator\Annotator;
use Zerotoprod\DocblockAnnotator\DocblockAnnotator;

class DocblockAnnotatorUpdateFileTest extends TestCase
{

    /** @test */
    public function it_updates_docblock_for_a_single_file(): void
    {
        $phpCode = <<<PHP
        <?php
        class Foo
        {
            public function bar()
            {
                return 'hello';
            }
        }
        PHP;

        $filePath = vfsStream::url('root/Foo.php');
        file_put_contents($filePath, $phpCode);

        DocblockAnnotator::updateFile(
            $filePath,
            ['Added docblock'],
            [Annotator::public],
            [Annotator::method]
        );

        $updated = file_get_contents($filePath);

        $expected = <<<PHP
        <?php
        class Foo
        {
            /**
             * Added docblock
             */
            public function bar()
            {
                return 'hello';
            }
        }
        PHP;

        $this->assertEquals($expected, $updated);
    }

    /** @test */
    public function it_calls_success_callback_when_docblock_is_updated(): void
    {
        $phpCode = <<<PHP
        <?php
        class Foo
        {
            public function bar()
            {
                return 'hello';
            }
        }
        PHP;

        $filePath = vfsStream::url('root/Foo.php');
        file_put_contents($filePath, $phpCode);

        $successCalled = false;
        $successCallback = function (string $file, string $newCode) use (&$successCalled) {
            $this->assertStringContainsString('Added docblock', $newCode);
            $this->assertEquals(vfsStream::url('root/Foo.php'), $file);
            $successCalled = true;
        };

        DocblockAnnotator::updateFile(
            $filePath,
            ['Added docblock'],
            [Annotator::public],
            [Annotator::method],
            $successCallback
        );

        $this->assertTrue($successCalled);
    }

    /** @test */
    public function it_does_not_call_success_callback_when_no_changes_occur(): void
    {
        $phpCode = <<<PHP
        <?php
        class Foo
        {
            /**
             * Already present
             */
            public function bar()
            {
                return 'hello';
            }
        }
        PHP;

        $filePath = vfsStream::url('root/Foo.php');
        file_put_contents($filePath, $phpCode);

        $successCalled = false;
        $successCallback = static function () use (&$successCalled) {
            $successCalled = true;
        };

        DocblockAnnotator::updateFile(
            $filePath,
            ['Already present'],
            [Annotator::public],
            [Annotator::method],
            $successCallback
        );

        $this->assertFalse($successCalled);
    }
}