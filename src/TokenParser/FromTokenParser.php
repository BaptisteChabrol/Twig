<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\TokenParser;

use Twig\Node\Expression\AssignNameExpression;
use Twig\Node\ImportNode;
use Twig\Node\Node;
use Twig\Token;

/**
 * Imports macros.
 *
 *   {% from 'forms.html' import forms %}
 *
 * @internal
 */
final class FromTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $macro = $this->parser->getExpressionParser()->parseExpression();
        $stream = $this->parser->getStream();
        $stream->expect(Token::NAME_TYPE, 'import');

        $targets = [];
        while (true) {
            $name = $stream->expect(Token::NAME_TYPE)->getValue();

            if ($stream->nextIf('as')) {
                $alias = new AssignNameExpression($stream->expect(Token::NAME_TYPE)->getValue(), $token->getLine());
            } else {
                $alias = new AssignNameExpression($name, $token->getLine());
            }

            $targets[$name] = $alias;

            if (!$stream->nextIf(Token::PUNCTUATION_TYPE, ',')) {
                break;
            }
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $internalRef = $this->parser->getVarName();
        $node = new ImportNode($macro, $internalRef, $token->getLine(), $this->parser->isMainScope());

        foreach ($targets as $name => $alias) {
            $this->parser->addImportedSymbol('function', $alias->getAttribute('name'), 'macro_'.$name, $internalRef);
        }

        return $node;
    }

    public function getTag(): string
    {
        return 'from';
    }
}
