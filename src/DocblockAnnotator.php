<?php

namespace Zerotoprod\DocblockAnnotator;

use Closure;
use PhpParser\NodeVisitorAbstract;
use Throwable;
use Zerotoprod\Filesystem\Filesystem;

/**
 * @link https://github.com/zero-to-prod/docblock-annotator
 */
class DocblockAnnotator extends NodeVisitorAbstract
{
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public static function update(
        string $directory,
        array $comments,
        array $visibility = [Annotator::public],
        array $members = [Annotator::method, Annotator::property, Annotator::constant],
        ?Closure $success = null,
        ?Closure $failure = null,
        bool $recursive = true
    ): void {
        $files = $recursive
            ? Filesystem::getFilesByExtensionRecursive($directory, 'php')
            : Filesystem::getFilesByExtension($directory, 'php');

        foreach ($files as $file) {
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
}