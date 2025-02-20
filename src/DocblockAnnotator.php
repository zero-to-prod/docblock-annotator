<?php

namespace Zerotoprod\DocblockAnnotator;

use Closure;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use SplFileInfo;
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
     * Instantiates the class.
     *
     * @param  array         $modifiers   Visibility levels to target (default: [Modifier::public])
     * @param  array         $statements  Statement types to target (default: various class elements)
     * @param  Closure|null  $success     Callback function to execute on successful update
     * @param  Closure|null  $failure     Callback function to execute if an error occurs
     * @param  Parser|null   $Parser      Parser instance to parse PHP code into a node tree
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function __construct(
        private readonly array $modifiers = [Modifier::public],
        private readonly array $statements = [
            Statement::ClassMethod,
            Statement::Const_,
            Statement::Class_,
            Statement::ClassConst,
            Statement::EnumCase,
            Statement::Enum_,
            Statement::Function_,
            Statement::Trait_,
            Statement::Property,
            Statement::Interface_,
        ],
        private readonly ?Closure $success = null,
        private readonly ?Closure $failure = null,
        private readonly ?Parser $Parser = null
    ) {
    }

    /**
     * Updates docblocks in all PHP files within a directory.
     *
     * @param  array   $comments   The comments to add to docblocks
     * @param  string  $directory  The directory path to process
     * @param  bool    $recursive  Whether to process files recursively (default: true)
     *
     * @return void
     * @throws Throwable
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function updateDirectory(
        array $comments,
        string $directory,
        bool $recursive = true,
    ): void {
        $this->updateFiles(
            $comments,
            $recursive
                ? Filesystem::getFilesByExtensionRecursive($directory, 'php')
                : Filesystem::getFilesByExtension($directory, 'php')
        );
    }

    /**
     * Updates docblocks for a specified array of files.
     *
     * @param  array  $comments  The comments to add to docblocks
     * @param  array  $files     Array of file paths to update
     *
     * @return void
     * @throws Throwable
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function updateFiles(array $comments, array $files = []): void
    {
        foreach ($files as $file) {
            try {
                // Determine the file path based on type
                $filePath = $file instanceof SplFileInfo ? $file->getPathname() : $file;

                // Skip if the path is empty or invalid
                if (empty($filePath)) {
                    continue;
                }

                $code = file_get_contents($filePath);
                if ($code === false) {
                    continue; // Skip if file cannot be read
                }

                $value = (new Annotator(
                    $comments,
                    $this->modifiers,
                    $this->statements,
                    $this->Parser
                ))->process($code);

                if ($value !== $code) {
                    file_put_contents($filePath, $value);
                    if ($this->success) {
                        ($this->success)($filePath, $value);
                    }
                }
            } catch (Throwable $throwable) {
                if ($this->failure) {
                    ($this->failure)($throwable->getMessage());
                } else {
                    throw $throwable;
                }
            }
        }
    }
}