<?php

namespace Zerotoprod\DocblockAnnotator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class DocblockTraverser
{
    /**
     * Parse and traverse the code. For each node:
     *   - the callback receives the node
     *   - it returns an array of lines (strings) to add
     *   - those lines are inserted/merged into the existing docblock
     *   - updates are stored and applied at the end
     *
     * @param  string                 $code      The PHP code to parse.
     * @param  callable(Node): array  $callback  Receives a Node, returns doc lines to add.
     *
     * @return string The code with updated docblocks.
     */
    public function update(string $code, callable $callback): string
    {
        $parser = (new ParserFactory)->createForHostVersion();
        $ast = $parser->parse($code);
        $changes = [];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(
            new class($callback, $changes) extends NodeVisitorAbstract {
                /** @var callable(Node): array */
                private $callback;

                /** @var Change[] */
                private array $changes;

                public function __construct(callable $callback, array &$changes)
                {
                    $this->callback = $callback;
                    $this->changes = &$changes;
                }

                public function enterNode(Node $node): void
                {
                    $newLines = ($this->callback)($node);

                    if (!is_array($newLines) || count($newLines) === 0) {
                        return;
                    }

                    $comment = $node->getDocComment();
                    if ($comment) {
                        $existing = $comment->getText();
                        $updatedDoc = $this->formatDoc($existing, $newLines, $this->shouldIndent($node));

                        $this->changes[] = Change::from([
                            Change::start => $comment->getStartFilePos(),
                            Change::end => $comment->getEndFilePos(),
                            Change::text => $updatedDoc,
                        ]);
                    } else {
                        $docText = $this->createDocblock($newLines, $node);
                        $start = $node->getStartFilePos();

                        $this->changes[] = Change::from([
                            Change::start => $start,
                            Change::end => $start - 1,
                            Change::text => $docText,
                        ]);
                    }
                }

                private function shouldIndent(Node $node): bool
                {
                    return !($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Enum_);
                }

                private function createDocblock(array $lines, Node $node): string
                {
                    $indent = $this->shouldIndent($node);
                    $asterisk = $indent ? '     * ' : ' * ';
                    $closing = $indent ? '     */' : ' */';
                    $padding = $indent ? "\n    " : "\n";

                    $doc = "/**\n";
                    foreach ($lines as $line) {
                        $doc .= "$asterisk$line\n";
                    }
                    $doc .= "$closing$padding";

                    return $doc;
                }

                private function formatDoc(string $existingDoc, array $newLines, bool $indent): string
                {
                    $asterisk = $indent ? '     * ' : ' * ';
                    $closing = $indent ? '     */' : ' */';

                    if (!str_contains($existingDoc, "\n")) {
                        $content = trim(substr($existingDoc, 3, -2));
                        $doc = "/**\n$asterisk$content";

                        foreach ($newLines as $nl) {
                            $doc .= "\n$asterisk$nl";
                        }

                        return $doc."\n$closing";
                    }

                    $doc = rtrim($existingDoc, " */\n");
                    foreach ($newLines as $nl) {
                        $doc .= "\n$asterisk$nl";
                    }

                    return $doc."\n$closing";
                }
            }
        );

        $traverser->traverse($ast);

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
}