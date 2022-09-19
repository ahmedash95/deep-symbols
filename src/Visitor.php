<?php

namespace DeepSymbols;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class Visitor extends NodeVisitorAbstract
{
    private ?Parser $parser;

    public function __construct(?Parser $parser)
    {
        $this->parser = $parser;
    }

    public function enterNode(Node $node)
    {
        if (in_array($node->getType(), ['Stmt_Class', 'Stmt_Trait'])) {
            $class = $node->namespacedName->toString();

            $this->parser->getIndexer()->setClass($class, $this->parser->getCurrentFilePath());

            $extends = null;
            if (isset($node->extends)) {
                $extends = $node->extends !== null ? implode('\\', $node->extends->parts) : null;
            }

            if ($extends != null) {
                $this->parser->getIndexer()->setInheritance($class, $extends);
                if ($this->parser->getLoader()->findFile($extends)) {
                    $path = realpath($this->parser->getLoader()->findFile($extends));
                    $path = str_replace($this->parser->getBasePath(), '', $path);
                    $this->parser->parse($path);
                }
            }

            foreach ($node->stmts as $statement) {
                if ($statement instanceof Node\Stmt\ClassMethod) {
                    $this->parser->getIndexer()->setMember($class, $statement->name->name, $statement->name->getAttribute('startLine'), 'Method');
                } elseif ($statement instanceof Node\Stmt\Property) {
                    $property = $statement->props[0];
                    $this->parser->getIndexer()->setMember($class, $property->name, $property->getAttribute('startLine'), 'Property');
                } elseif ($statement instanceof Node\Stmt\TraitUse) {
                    foreach ($statement->traits as $node) {
                        $trait = implode('\\', $node->parts);
                        $this->parser->getIndexer()->setInheritance($class, $trait);
                        if ($this->parser->getLoader()->findFile($trait)) {
                            $path = realpath($this->parser->getLoader()->findFile($trait));
                            $path = str_replace($this->parser->getBasePath(), '', $path);
                            $this->parser->parse($path);
                        }
                    }
                }
            }
        }
    }
}
