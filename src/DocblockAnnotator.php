<?php

namespace Zerotoprod\DocblockAnnotator;

use Closure;
use PhpParser\NodeVisitorAbstract;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class DocblockAnnotator extends NodeVisitorAbstract
{
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public static function update(
        string $dir,
        array $comments,
        array $visibility = [Annotator::public],
        array $members = [Annotator::method, Annotator::property, Annotator::constant],
        ?Closure $success = null,
        ?Closure $failure = null
    ): void {
        foreach (self::getFilesByExtension($dir, 'php') as $file) {
            try {
                $code = file_get_contents($file);
                if ($code === false) {
                    continue;
                }

                $value = (new Annotator($comments, $visibility, $members))->process($code);

                if ($value !== $code) {
                    file_put_contents($file, $value);
                    if ($success) {
                        $success($file, $value);
                    }
                }
            } catch (Throwable $Throwable) {
                if ($failure) {
                    $failure($Throwable->getMessage());
                }
            }
        }
    }

    private static function getFilesByExtension(string $directory, string $extension): array
    {
        return array_filter(
            iterator_to_array(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($directory)
                )
            ),
            static fn(SplFileInfo $SplFileInfo) => !$SplFileInfo->isDir() && $SplFileInfo->getExtension() === $extension
        );
    }
}