<?php

namespace Zerotoprod\DocblockAnnotator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class Annotator extends NodeVisitorAbstract
{
    public const method = 'method';
    public const property = 'property';
    public const constant = 'constant';
    public const public = 'public';
    public const private = 'private';
    public const protected = 'protected';

    /** @var Change[] */
    private array $changes = [];
    private array $comments;
    private array $visibility;
    private array $members;

    public function __construct(
        array $comments,
        array $visibility = [self::public],
        array $members = [self::method, self::property, self::constant]
    ) {
        $this->comments = $comments;
        $this->visibility = array_map('strtolower', $visibility);
        $this->members = array_map('strtolower', $members);
    }

    public function process(string $code): string
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse(
            (new ParserFactory)->createForHostVersion()->parse($code)
        );

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

    private function hasEquivalentCommentLine(string $existing_comment, string $comment): bool
    {
        $normalized = str_replace(' ', '', $comment);
        foreach (explode("\n", $existing_comment) as $line) {
            if (strpos($line, $normalized) !== false) {
                return true;
            }
        }

        return false;
    }

    private function format(string $existing_doc, array $new_comments): string
    {
        if (strpos($existing_doc, "\n") === false) {
            $existing_content = trim(substr($existing_doc, 3, -2));
            $doc = "/**\n";
            $doc .= "     * ".$existing_content;

            foreach ($new_comments as $comment) {
                $doc .= "\n     * ".$comment;
            }

            $doc .= "\n     */";

            return $doc;
        }

        $doc = rtrim($existing_doc, " */\n");
        foreach ($new_comments as $comment) {
            $doc .= "\n     * ".$comment;
        }
        $doc .= "\n     */";

        return $doc;
    }

    private function processComment(Node $node): void
    {
        $doc_comment = $node->getDocComment();
        if ($doc_comment) {
            $existing_doc = $doc_comment->getText();
            $new_comments = [];

            foreach ($this->comments as $comment) {
                if (!$this->hasEquivalentCommentLine($existing_doc, $comment)) {
                    $new_comments[] = $comment;
                }
            }

            if (!empty($new_comments)) {
                $new_doc = $this->format($existing_doc, $new_comments);
                $this->changes[] = Change::from([
                    Change::start => $doc_comment->getStartFilePos(),
                    Change::end => $doc_comment->getEndFilePos(),
                    Change::text => $new_doc
                ]);
            }
        } else {
            $text = "/**\n";
            foreach ($this->comments as $comment) {
                $text .= "     * $comment\n";
            }
            $text .= "     */\n    ";
            $this->changes[] = Change::from([
                Change::start => $node->getStartFilePos(),
                Change::end => $node->getStartFilePos() - 1,
                Change::text => $text
            ]);
        }
    }

    private function hasMatchingVisibility(Node $node): bool
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            return (
                (in_array(self::public, $this->visibility, true) && $node->isPublic())
                || (in_array(self::protected, $this->visibility, true) && $node->isProtected())
                || (in_array(self::private, $this->visibility, true) && $node->isPrivate())
            );
        }

        if ($node instanceof Node\Stmt\Property) {
            return (
                (in_array(self::public, $this->visibility, true) && $node->isPublic())
                || (in_array(self::protected, $this->visibility, true) && $node->isProtected())
                || (in_array(self::private, $this->visibility, true) && $node->isPrivate())
            );
        }

        if ($node instanceof Node\Stmt\ClassConst) {
            return (
                (in_array(self::public, $this->visibility, true) && $node->isPublic())
                || (in_array(self::protected, $this->visibility, true) && $node->isProtected())
                || (in_array(self::private, $this->visibility, true) && $node->isPrivate())
            );
        }

        return false;
    }

    private function hasMatchingMemberType(Node $node): bool
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            return in_array(self::method, $this->members, true);
        }

        if ($node instanceof Node\Stmt\Property) {
            return in_array(self::property, $this->members, true);
        }

        if ($node instanceof Node\Stmt\ClassConst) {
            return in_array(self::constant, $this->members, true);
        }

        return false;
    }

    public function enterNode(Node $node): void
    {
        if (($node instanceof Node\Stmt\ClassMethod
                || $node instanceof Node\Stmt\Property
                || $node instanceof Node\Stmt\ClassConst)
            && $this->hasMatchingVisibility($node)
            && $this->hasMatchingMemberType($node)
        ) {
            $this->processComment($node);
        }
    }
}