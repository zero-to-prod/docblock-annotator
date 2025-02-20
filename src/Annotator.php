<?php

namespace Zerotoprod\DocblockAnnotator;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionClass;
use Zerotoprod\DocgenVisitor\Change;
use Zerotoprod\DocgenVisitor\DocgenVisitor;

/**
 * Processes the given PHP code by adding comments to the specified members.
 *
 * @link https://github.com/zero-to-prod/docblock-annotator
 */
class Annotator extends NodeVisitorAbstract
{

    /**
     * @var string[] Lines you want added to the docblock.
     */
    private array $comments;

    /**
     * @var Modifier[] Visibility levels to target (public, private, protected).
     */
    private array $modifiers;

    /**
     * @var Statement[] Member types to target (method, property, constant, class, enum, enum_case).
     */
    private array $statements;

    /**
     * Initializes the Annotator with comments, visibility, and member types.
     *
     * @param  array              $comments    Lines you want added to the docblock.
     * @param  array|Modifier[]   $modifiers   The visibility levels you want to target (public, private, protected).
     * @param  array|Statement[]  $statements  The member types you want to target (method, property, constant, class, enum, enum_case).
     * @param ?Parser             $Parser      Parses PHP code into a node tree.
     *
     * @link https://github.com/zero-to-prod/docblock-annotator
     */
    public function __construct(
        array $comments,
        array $modifiers = [Modifier::public],
        array $statements = [Statement::ClassMethod, Statement::Property, Statement::Const_, Statement::Function_, Statement::ClassConst],
        private readonly ?Parser $Parser = null
    ) {
        $this->comments = $comments;
        $this->modifiers = array_filter(array_map(static fn(mixed $value) => $value instanceof Modifier
            ? $value
            : Modifier::tryFrom(strtolower($value)), $modifiers));
        $this->statements = array_filter(array_map(static fn(mixed $value) => $value instanceof Statement
            ? $value
            : Statement::tryFrom(strtolower($value)), $statements));
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
        /** @var Change[] $changes */
        $changes = [];

        $NodeTraverser = new NodeTraverser();
        $NodeTraverser->addVisitor(
            new DocgenVisitor(
                fn(Node $Node) => $this->getCommentLinesForIncludedNode($Node),
                $changes
            )
        );
        $NodeTraverser->traverse(
            $this->Parser
                ? $this->Parser->parse($code)
                : (new ParserFactory)->createForHostVersion()->parse($code)
        );

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

    /**
     * Returns comment lines for supported nodes.
     */
    private function getCommentLinesForIncludedNode(Node $Node): array
    {
        $node_type = (new ReflectionClass($Node))->getShortName();
        $statement_type = defined(Statement::class."::$node_type")
            ? constant(Statement::class."::$node_type")
            : null;

        $unsupported_statement = !$statement_type || !in_array($statement_type, $this->statements, true);

        if ($unsupported_statement) {
            return [];
        }

        $validNode = $Node instanceof Node\Stmt\Class_
            || $Node instanceof Node\Stmt\Enum_
            || $Node instanceof Node\Stmt\Interface_
            || $Node instanceof Node\Stmt\Trait_
            || $Node instanceof Node\Stmt\Function_
            || $this->hasMatchingVisibility($Node);

        return $validNode
            ? $this->dedupe($Node->getDocComment()?->getReformattedText() ?? '')
            : [];
    }

    /**
     * Checks if a node's visibility matches the configured visibility levels.
     */
    private function hasMatchingVisibility(Node $Node): bool
    {
        if ($Node instanceof Node\Stmt\Const_ || $Node instanceof Node\Stmt\Function_) {
            return true;
        }

        if ($Node instanceof Node\Stmt\EnumCase) {
            return in_array(Modifier::public, $this->modifiers, true);
        }

        $visibility = match (true) {
            method_exists($Node, 'isPublic') && $Node->isPublic() => Modifier::public,
            method_exists($Node, 'isProtected') && $Node->isProtected() => Modifier::protected,
            method_exists($Node, 'isPrivate') && $Node->isPrivate() => Modifier::private,
            default => null
        };

        return $visibility !== null && in_array($visibility, $this->modifiers, true);
    }

    /**
     * Removes duplicate comments by comparing existing docblock text with new comments.
     */
    private function dedupe(string $text): array
    {
        return array_filter(
            $this->comments,
            static fn($line) => !str_contains(
                preg_replace('/\s+/', '', $text),
                preg_replace('/\s+/', '', $line)
            )
        );
    }
}