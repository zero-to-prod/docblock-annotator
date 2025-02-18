<?php

namespace Zerotoprod\DocblockAnnotator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Zerotoprod\DocgenVisitor\DocgenVisitor;

/**
 * Processes the given PHP code by adding comments to the specified members.
 *
 * @link https://github.com/zero-to-prod/docblock-annotator
 */
class Annotator extends NodeVisitorAbstract
{
    /**
     * Indicates that the member type is a method.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const method = 'method';
    /**
     * Indicates that the member type is a property.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const property = 'property';
    /**
     * Indicates that the member type is a constant.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const constant = 'constant';
    /**
     * Indicates that the member type is a class.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const class_ = 'class';
    /**
     * Indicates that the member type is an interface.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const interface_ = 'interface';

    /**
     * Indicates that the member type is a trait.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const trait_ = 'trait';
    /**
     * Indicates that the member type is an enum.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const enum = 'enum';
    /**
     * Indicates that the member type is an enum case.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const enum_case = 'enum_case';

    /**
     * Indicates that the visibility is public.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const public = 'public';
    /**
     * Indicates that the visibility is private.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const private = 'private';
    /**
     * Indicates that the visibility is protected.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public const protected = 'protected';

    /**
     * @var string[] Lines you want added to the docblock.
     */
    private array $comments;

    /**
     * @var string[] Visibility levels to target (public, private, protected).
     */
    private array $visibility;

    /**
     * @var string[] Member types to target (method, property, constant, class, enum, enum_case).
     */
    private array $members;

    /**
     * Initializes the Annotator with comments, visibility, and member types.
     *
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
     * Processes the given PHP code by adding comments to the specified members.
     *
     * @param  string  $code  The PHP code to process.
     *
     * @return string The processed PHP code with added comments.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function process(string $code): string
    {
        $NodeTraverser = new NodeTraverser();
        $changes = [];
        $NodeTraverser->addVisitor(new DocgenVisitor(function (Node $Node) {
            $unknown_node = !in_array(
                match (true) {
                    $Node instanceof Node\Stmt\ClassMethod => self::method,
                    $Node instanceof Node\Stmt\Property => self::property,
                    $Node instanceof Node\Stmt\ClassConst => self::constant,
                    $Node instanceof Node\Stmt\Class_ => self::class_,
                    $Node instanceof Node\Stmt\Enum_ => self::enum,
                    $Node instanceof Node\Stmt\EnumCase => self::enum_case,
                    $Node instanceof Node\Stmt\Interface_ => self::interface_,
                    $Node instanceof Node\Stmt\Trait_ => self::trait_,
                    default => null
                },
                $this->members,
                true
            );

            if ($unknown_node) {
                return [];
            }

            return $Node instanceof Node\Stmt\Class_
            || $Node instanceof Node\Stmt\Enum_
            || $Node instanceof Node\Stmt\Interface_
            || $Node instanceof Node\Stmt\Trait_
            || $this->hasMatchingVisibility($Node)
                ? $this->dedupe(
                    $Node->getDocComment()
                        ? $Node->getDocComment()->getText()
                        : ''
                )
                : [];
        }, $changes));

        $NodeTraverser->traverse((new ParserFactory)->createForHostVersion()->parse($code));

        foreach (array_reverse($changes) as $change) {
            $code = substr_replace(
                $code,
                $change->text,
                $change->start,
                $change->end - $change->start + 1
            );
        }

        return $code;
    }

    private function hasMatchingVisibility(Node $Node): bool
    {
        if ($Node instanceof Node\Stmt\EnumCase) {
            return in_array(self::public, $this->visibility, true);
        }

        $visibility = match (true) {
            $Node->isPublic() => self::public,
            $Node->isProtected() => self::protected,
            $Node->isPrivate() => self::private,
            default => null
        };

        return $visibility !== null && in_array($visibility, $this->visibility, true);
    }

    private function dedupe(string $docblock): array
    {
        return array_filter(
            $this->comments,
            static fn($line) => !str_contains(
                preg_replace('/\s+/', '', $docblock),
                preg_replace('/\s+/', '', $line)
            )
        );
    }
}