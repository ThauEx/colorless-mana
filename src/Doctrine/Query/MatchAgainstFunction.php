<?php

namespace App\Doctrine\Query;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * "MATCH_AGAINST" "(" StringPrimary {"," StringPrimary}* ")"
 *
 * All arguments but the last are the columns to search, matching the
 * FULLTEXT index; the last argument is the search term.
 */
class MatchAgainstFunction extends FunctionNode
{
    /** @var Node[] */
    private array $args = [];

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->args[] = $parser->StringPrimary();

        while ($parser->getLexer()->isNextToken(TokenType::T_COMMA)) {
            $parser->match(TokenType::T_COMMA);
            $this->args[] = $parser->StringPrimary();
        }

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $termIndex = count($this->args) - 1;

        $columns = array_map(
            static fn (Node $arg) => $sqlWalker->walkStringPrimary($arg),
            array_slice($this->args, 0, $termIndex)
        );
        $term = $sqlWalker->walkStringPrimary($this->args[$termIndex]);

        return sprintf('MATCH(%s) AGAINST(%s IN BOOLEAN MODE)', implode(', ', $columns), $term);
    }

    /**
     * Turns free-text user input into a boolean-mode search string: every
     * word becomes required (+) and matches as a prefix (*), so additional
     * words narrow the result and partially typed words still match.
     */
    public static function toBooleanSearchTerm(string $term): string
    {
        $words = array_map(
            static fn (string $word) => preg_replace('/[+\-<>()~*"@]/', '', $word),
            preg_split('/\s+/', trim($term))
        );
        $words = array_filter($words, static fn (string $word) => $word !== '');

        return implode(' ', array_map(static fn (string $word) => "+{$word}*", $words));
    }
}
