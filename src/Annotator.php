<?php

namespace Zerotoprod\DocblockAnnotator;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @link https://github.com/zero-to-prod/docblock-annotator
 */
class Annotator extends NodeVisitorAbstract
{
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const method = 'method';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const property = 'property';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const constant = 'constant';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const class_ = 'class';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const enum = 'enum';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const enum_case = 'enum_case';

    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const public = 'public';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const private = 'private';
    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const protected = 'protected';

    /** @var string[] Lines you want added to the docblock. */
    private array $comments;

    /** @var string[] Visibility levels to target (public, private, protected). */
    private array $visibility;

    /** @var string[] Member types to target (method, property, constant, class, enum, enum_case). */
    private array $members;

    /**
     * @param  array  $comments    Lines you want added to the docblock.
     * @param  array  $visibility  The visibility levels you want to target (public, private, protected).
     * @param  array  $members     The member types you want to target (method, property, constant, class, enum, enum_case).
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function __construct(
        array $comments,
        array $visibility = [self::public],
        array $members = [self::method, self::property, self::constant]
    ) {
        $this->comments = $comments;
        $this->visibility = array_map('strtolower', $visibility);
        $this->members = array_map('strtolower', $members);
    }

    /**
     * Add lines to docblocks using DocblockTraverser.
     *
     * We now handle duplicate filtering here:
     *   - We read the existing docblock from the node (if any),
     *   - We filter out lines that already exist,
     *   - Then we pass the (potentially reduced) lines to DocblockTraverser.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function process(string $code): string
    {
        return (new DocblockTraverser())->update($code, function (Node $Node): array {
            if (!$this->hasMatchingMemberType($Node)) {
                return [];
            }

            if ($Node instanceof Node\Stmt\Class_ || $Node instanceof Node\Stmt\Enum_) {
                $docblock = $Node->getDocComment() ? $Node->getDocComment()->getText() : '';

                return self::filterDuplicates($docblock, $this->comments);
            }

            if ($this->hasMatchingVisibility($Node)) {
                $docblock = $Node->getDocComment() ? $Node->getDocComment()->getText() : '';

                return self::filterDuplicates($docblock, $this->comments);
            }

            return [];
        });
    }

    private function hasMatchingMemberType(Node $Node): bool
    {
        if ($Node instanceof Node\Stmt\ClassMethod) {
            return in_array(self::method, $this->members, true);
        }

        if ($Node instanceof Node\Stmt\Property) {
            return in_array(self::property, $this->members, true);
        }

        if ($Node instanceof Node\Stmt\ClassConst) {
            return in_array(self::constant, $this->members, true);
        }

        if ($Node instanceof Node\Stmt\Class_) {
            return in_array(self::class_, $this->members, true);
        }

        if ($Node instanceof Node\Stmt\Enum_) {
            return in_array(self::enum, $this->members, true);
        }

        if ($Node instanceof Node\Stmt\EnumCase) {
            return in_array(self::enum_case, $this->members, true);
        }

        return false;
    }

    private function hasMatchingVisibility(Node $Node): bool
    {
        if ($Node instanceof Node\Stmt\EnumCase) {
            return in_array(self::public, $this->visibility, true);
        }

        $isPublic = in_array(self::public, $this->visibility, true);
        $isProtected = in_array(self::protected, $this->visibility, true);
        $isPrivate = in_array(self::private, $this->visibility, true);

        return (
            ($Node instanceof Node\Stmt\ClassMethod
                && (
                    ($isPublic && $Node->isPublic())
                    || ($isProtected && $Node->isProtected())
                    || ($isPrivate && $Node->isPrivate())
                )
            )
            || ($Node instanceof Node\Stmt\Property
                && (
                    ($isPublic && $Node->isPublic())
                    || ($isProtected && $Node->isProtected())
                    || ($isPrivate && $Node->isPrivate())
                )
            )
            || ($Node instanceof Node\Stmt\ClassConst
                && (
                    ($isPublic && $Node->isPublic())
                    || ($isProtected && $Node->isProtected())
                    || ($isPrivate && $Node->isPrivate())
                )
            )
        );
    }

    private static function filterDuplicates(string $docblock, array $comments): array
    {
        $lines = [];
        foreach ($comments as $line) {
            if (!self::commentContains($docblock, trim(str_replace(' ', '', $line)))) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    private static function commentContains(string $docblock, string $needle): bool
    {
        foreach (explode("\n", $docblock) as $line) {
            if (str_contains(trim(str_replace(' ', '', $line)), $needle)) {
                return true;
            }
        }

        return false;
    }
}