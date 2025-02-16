<?php

namespace Zerotoprod\DocblockAnnotator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class Annotator extends NodeVisitorAbstract
{
    /** @var Change[] */
    private array $changes = [];
    private array $comments;

    public function __construct(array $comments)
    {
        $this->comments = $comments;
    }

    public function process(string $code): string
    {
        $parser = (new ParserFactory)->createForHostVersion();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);

        $traverser->traverse($parser->parse($code));

        $changes = array_reverse($this->changes);
        foreach ($changes as $change) {
            $code = substr_replace(
                $code,
                $change->text,
                $change->start,
                $change->end - $change->start + 1
            );
        }

        return $code;
    }

    private function hasEquivalentComment(string $existing_comment, string $comment): bool
    {
        $normalized = str_replace(' ', '', $comment);
        foreach (explode("\n", $existing_comment) as $line) {
            if (strpos($line, $normalized) !== false) {
                return true;
            }
        }

        return false;
    }

    private function formatDocComment(string $existing_doc, array $new_comments): string
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

    public function enterNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->isPublic()) {
            $doc_comment = $node->getDocComment();
            if ($doc_comment) {
                $existing_doc = $doc_comment->getText();
                $new_comments = [];

                foreach ($this->comments as $comment) {
                    if (!$this->hasEquivalentComment($existing_doc, $comment)) {
                        $new_comments[] = $comment;
                    }
                }

                if (!empty($new_comments)) {
                    $new_doc = $this->formatDocComment($existing_doc, $new_comments);
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
    }
}