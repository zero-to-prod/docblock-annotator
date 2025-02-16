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
    public function update(string $dir, array $comments, ?Closure $success = null, ?Closure $failure = null): void
    {
        $Annotator = new Annotator($comments);
        foreach (self::getFilesByExtension($dir, 'php') as $file) {
            try {
                $code = file_get_contents($file);
                if ($code === false) {
                    continue;
                }

                $new_code = $Annotator->process($code);

                if ($new_code !== $code) {
                    file_put_contents($file, $new_code);
                    if ($success) {
                        $success($file, $new_code);
                    }
                }
            } catch (Throwable $e) {
                if ($failure) {
                    $failure($e);
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