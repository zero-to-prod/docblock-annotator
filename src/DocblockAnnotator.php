<?php

namespace Zerotoprod\DocblockAnnotator;

use Closure;
use PhpParser\NodeVisitorAbstract;
use Throwable;
use Zerotoprod\Filesystem\Filesystem;

/**
 * Annotates PHP docblocks in files or directories.
 *
 * This class provides methods to update docblocks by adding specified comments to PHP
 * classes, methods, properties, etc., based on visibility and member type criteria.
 *
 * @link https://github.com/zero-to-prod/docblock-annotator
 */
class DocblockAnnotator extends NodeVisitorAbstract
{
    /**
     * Updates docblocks in all PHP files within a directory.
     *
     * @param  string        $directory   The directory path to process.
     * @param  array         $comments    The comments to add to docblocks.
     * @param  array         $visibility  Visibility levels to target; defaults to public only.
     * @param  array         $members     Member types to target; defaults to methods, properties, and constants.
     * @param  Closure|null  $success     Callback function to execute on successful update.
     * @param  Closure|null  $failure     Callback function to execute if an error occurs.
     * @param  bool          $recursive   Whether to process files recursively in the directory; defaults to true.
     *
     * @return void
     * @throws Throwable
     */
    public static function updateDirectory(
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
            self::updateFile($file, $comments, $visibility, $members, $success, $failure);
        }
    }

    /**
     * Updates docblocks for a specified array of files.
     *
     * @param  array         $files       An array of file paths to update.
     * @param  array         $comments    The comments to add to docblocks.
     * @param  array         $visibility  Visibility levels to target; defaults to public only.
     * @param  array         $members     Member types to target; defaults to methods, properties, and constants.
     * @param  Closure|null  $success     Callback function to execute on successful update.
     * @param  Closure|null  $failure     Callback function to execute if an error occurs.
     *
     * @return void
     * @throws Throwable
     */
    public static function updateFiles(
        array $files,
        array $comments,
        array $visibility = [Annotator::public],
        array $members = [Annotator::method, Annotator::property, Annotator::constant],
        ?Closure $success = null,
        ?Closure $failure = null
    ): void {
        foreach ($files as $file) {
            self::updateFile($file, $comments, $visibility, $members, $success, $failure);
        }
    }

    /**
     * Updates the docblock for a single file.
     *
     * @param  string        $file        The path to the file to update.
     * @param  array         $comments    The comments to add to docblocks.
     * @param  array         $visibility  Visibility levels to target; defaults to public only.
     * @param  array         $members     Member types to target; defaults to methods, properties, and constants.
     * @param  Closure|null  $success     Callback function to execute on successful update.
     * @param  Closure|null  $failure     Callback function to execute if an error occurs.
     *
     * @return void
     * @throws Throwable If an error occurs during file operations or code processing.
     */
    public static function updateFile(
        string $file,
        array $comments,
        array $visibility = [Annotator::public],
        array $members = [Annotator::method, Annotator::property, Annotator::constant],
        ?Closure $success = null,
        ?Closure $failure = null
    ): void {
        try {
            $code = file_get_contents($file);

            if ($code === false) {
                return;
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
            } else {
                throw new $Throwable;
            }
        }
    }
}