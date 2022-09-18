<?php

namespace DeepSymbols;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class Parser
{
    private ?string $class = null;
    private string $basePath;
    private string $file;

    public function getCurrentFilePath(): string
    {
        return $this->basePath . $this->file;
    }

    public function parse($basePath, $file)
    {
        $basePath = rtrim($basePath, '/') . '/';
        $file = str_replace($basePath, '', $file);
        $this->basePath = $basePath;
        $this->file = $file;
        $code = file_get_contents($this->getCurrentFilePath());
        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->traverse($ast);

        $traverser->addVisitor(new Visitor($this));
        $traverser->traverse($ast);

        if ($this->class) {
            return Indexer::getMembers($this->class);
        }

        return [];
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }
}
