<?php

namespace DeepSymbols;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Scalar\MagicConst\Class_;
use PhpParser\Node\Scalar\MagicConst\Trait_;

class Visitor extends NodeVisitorAbstract
{

    private ?Parser $parser;

    public function __construct(?Parser $parser)
    {
        $this->parser = $parser;
    }

    public function enterNode(Node $node)
    {
        $autoloader = require($this->parser->getBasePath() . 'vendor/autoload.php');

        if (in_array($node->getType(), ['Stmt_Class', 'Stmt_Trait'])) {
            $class = $node->namespacedName->toString();

            Indexer::setClass($class, $this->parser->getCurrentFilePath());
            if ($this->parser !== null) {
                $this->parser->setClass($class);
            }

            $extends = null;
            if(isset($node->extends)) {
                $extends = $node->extends !== null ? implode('\\', $node->extends->parts) : null;
            }

            if ($extends != null) {
                Indexer::setInheritance($class, $extends);
                if ($autoloader->findFile($extends)) {
                    $path = realpath($autoloader->findFile($extends));
                    $path = str_replace($this->parser->getBasePath(), '', $path);
                    (new Parser())->parse($this->parser->getBasePath(), $path);
                }
            }

            foreach ($node->stmts as $statement) {
                if ($statement instanceof Node\Stmt\ClassMethod) {
                    Indexer::setMember($class, $statement->name->name, $statement->name->getAttribute('startLine'), 'Method');
                } else if ($statement instanceof Node\Stmt\Property) {
                    $property = $statement->props[0];
                    Indexer::setMember($class, $property->name, $property->getAttribute('startLine'), 'Property');
		} else {
		    foreach($statement->traits as $node) {
			$trait = implode('\\', $node->parts);
			Indexer::setInheritance($class, $trait);
			if ($autoloader->findFile($trait)) {
			    $path = realpath($autoloader->findFile($trait));
			    $path = str_replace($this->parser->getBasePath(), '', $path);
			    (new Parser())->parse($this->parser->getBasePath(), $path);
			}
		    }
		}
            }
        }
    }
}
