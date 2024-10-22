<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Twig\Node;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\NameExpression;

/**
 * Represents an import node.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[YieldReady]
class ImportNode extends Node
{
    public function __construct(AbstractExpression $expr, string $var, int $lineno, bool $global = true)
    {
        $this->deprecateNode('var', new NameDeprecation('var', '3.15'));

        parent::__construct(['expr' => $expr, 'var' => $var], ['global' => $global, 'var' => $var], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$macros[')
            ->repr($this->getAttribute('var'))
            ->raw('] = ')
        ;

        if ($this->getAttribute('global')) {
            $compiler
                ->raw('$this->macros[')
                ->repr($this->getAttribute('var'))
                ->raw('] = ')
            ;
        }

        if ($this->getNode('expr') instanceof NameExpression && '_self' === $this->getNode('expr')->getAttribute('name')) {
            $compiler->raw('$this');
        } else {
            $compiler
                ->raw('$this->loadTemplate(')
                ->subcompile($this->getNode('expr'))
                ->raw(', ')
                ->repr($this->getTemplateName())
                ->raw(', ')
                ->repr($this->getTemplateLine())
                ->raw(')->unwrap()')
            ;
        }

        $compiler->raw(";\n");
    }
}
