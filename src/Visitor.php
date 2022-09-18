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
        $autoloader = require($this->parser->getBasePath().'vendor/autoload.php');

        if ($node->getType() == 'Stmt_Class') {
            $class = $node->namespacedName->toString();
            $extends = $node->extends !== null ? implode('\\', $node->extends->parts) : null;

            Indexer::setClass($class, $this->parser->getCurrentFilePath());
            if ($this->parser !== null) {
                $this->parser->setClass($class);
            }
            if ($extends != null) {
                Indexer::setInheritance($class, $extends);
                $path = realpath($autoloader->findFile($extends));
                $path = str_replace($this->parser->getBasePath(), '', $path);
                (new Parser())->parse($this->parser->getBasePath(), $path);
            }

            foreach ($node->stmts as $statement) {
                if ($statement instanceof Node\Stmt\ClassMethod) {
                    Indexer::setMember($class, $statement->name->name, $statement->name->getAttribute('startLine'));
                }
            }
        }
    }
}