<?php

namespace Zerotoprod\DocblockAnnotator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

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

    /** @var Change[] */
    private array $changes = [];
    private array $comments;
    private array $visibility;
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
     * Add lines to docblocks.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function process(string $code): string
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);

        $parser = (new ParserFactory)->createForHostVersion();
        $traverser->traverse($parser->parse($code));

        foreach (array_reverse($this->changes) as $change) {
            $code = substr_replace(
                $code,
                $change->text,
                $change->start,
                $change->end - $change->start + 1
            );
        }

        return $code;
    }

    private function hasEquivalentCommentLine(string $existing, string $new_line): bool
    {
        $normalizedNew = trim(str_replace(' ', '', $new_line));
        foreach (explode("\n", $existing) as $line) {
            if (str_contains(trim(str_replace(' ', '', $line)), $normalizedNew)) {
                return true;
            }
        }

        return false;
    }

    private function format(string $existing, array $comments, bool $indent): string
    {
        $asterisk = $indent ? '     * ' : ' * ';
        $closing = $indent ? '     */' : ' */';

        if (!str_contains($existing, "\n")) {
            $content = trim(substr($existing, 3, -2));
            $doc = "/**\n$asterisk$content";

            foreach ($comments as $comment) {
                $doc .= "\n$asterisk$comment";
            }

            return $doc."\n$closing";
        }

        $doc = rtrim($existing, " */\n");
        foreach ($comments as $comment) {
            $doc .= "\n$asterisk$comment";
        }

        return $doc."\n$closing";
    }

    private function renderComment(Node $Node, bool $indent = true): void
    {
        $comment = $Node->getDocComment();

        if ($comment) {
            $existing = $comment->getText();
            $new_lines = [];

            foreach ($this->comments as $line) {
                if (!$this->hasEquivalentCommentLine($existing, $line)) {
                    $new_lines[] = $line;
                }
            }

            if ($new_lines) {
                $updated = $this->format($existing, $new_lines, $indent);
                $this->changes[] = Change::from([
                    Change::start => $comment->getStartFilePos(),
                    Change::end => $comment->getEndFilePos(),
                    Change::text => $updated
                ]);
            }
        } else {
            $asterisk = $indent ? '     * ' : ' * ';
            $closing = $indent ? '     */' : ' */';
            $padding = $indent ? "\n    " : "\n";

            $text = "/**\n";
            foreach ($this->comments as $line) {
                $text .= "$asterisk$line\n";
            }
            $text .= "$closing$padding";

            $this->changes[] = Change::from([
                Change::start => $Node->getStartFilePos(),
                Change::end => $Node->getStartFilePos() - 1,
                Change::text => $text
            ]);
        }
    }

    private function hasMatchingVisibility(Node $Node): bool
    {
        if ($Node instanceof Node\Stmt\Class_ || $Node instanceof Node\Stmt\Enum_) {
            return true;
        }

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
                ))
            || ($Node instanceof Node\Stmt\Property
                && (
                    ($isPublic && $Node->isPublic())
                    || ($isProtected && $Node->isProtected())
                    || ($isPrivate && $Node->isPrivate())
                ))
            || ($Node instanceof Node\Stmt\ClassConst
                && (
                    ($isPublic && $Node->isPublic())
                    || ($isProtected && $Node->isProtected())
                    || ($isPrivate && $Node->isPrivate())
                ))
        );
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

    /**
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function enterNode(Node $node): void
    {
        if (!$this->hasMatchingMemberType($node)) {
            return;
        }

        if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Enum_) {
            $this->renderComment($node, false);

            return;
        }

        if ($this->hasMatchingVisibility($node)) {
            $this->renderComment($node);
        }
    }
}