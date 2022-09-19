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
    private $loader;
    private Indexer $indexer;

    public function __construct($basePath, $loader)
    {
        $basePath = rtrim($basePath, '/') . '/';
        $this->basePath = $basePath;
        $this->loader = $loader;
        $this->indexer = new Indexer();
    }

    public function newParser(): self
    {
        $instance = new self($this->basePath, $this->loader);
        $instance->setIndexer($this->indexer);
    
        return $instance;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function setIndexer(Indexer $indexer): self
    {
        $this->indexer = $indexer;

        return $this;
    }

    public function getIndexer(): Indexer
    {
        return $this->indexer;
    }

    public function getCurrentFilePath(): string
    {
        return $this->basePath . $this->file;
    }

    public function parse($file): array
    {
        $file = str_replace($this->basePath, '', $file);
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
            return $this->indexer->getMembers($this->class);
        }

        return [];
    }

    public function setClass($class): void
    {
        $this->class = $class;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
