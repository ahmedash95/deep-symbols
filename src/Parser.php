<?php

namespace DeepSymbols;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class Parser
{
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

    public function getLoader()
    {
        return $this->loader;
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

        $filePath = $this->getCurrentFilePath();

        if ((pathinfo($filePath)['extension'] ?? null) !== 'php') {
            return [];
        }

        $code = file_get_contents($filePath);
        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->traverse($ast);

        $traverser->addVisitor(new Visitor($this));
        $traverser->traverse($ast);

        if ($this->indexer->hasClasses()) {
            return $this->indexer->getMembers(array_key_first($this->indexer->getClasses()));
        }

        return [];
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
