<?php

namespace DeepSymbols;

class Indexer
{
    private array $classes = [];

    public function setClass($class, $path)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = [
                'path' => $path,
                'members' => [],
                'inheritance' => [],
            ];
        }
    }

    public function setInheritance($class, $parent)
    {
        $this->classes[$class]['inheritance'][] = $parent;
    }

    public function setMember($class, $member, $lineNumber, $type)
    {
        $this->classes[$class]['members'][] = [
            'name' => $member,
            'type' => $type,
            'start_line' => $lineNumber,
        ];
    }

    public function getMembers(string $class, bool $appendName = false): array
    {
        $members = $this->classes[$class]['members'] ?? [];

        foreach ($members as $index => $member) {
            $nameInPath = $appendName ? sprintf('[%s] ', self::getClassNameWithoutNamespace($class)) : '';
            $nameInPath .= sprintf('[%s] ', $member['type']) . $member['name'];
            $members[$index] = [
                'name' => $member['name'],
                'path' => $this->classes[$class]['path'] . ':' . $member['start_line'] . ':' . $nameInPath,
            ];
        }

        foreach ($this->classes[$class]['inheritance'] ?? [] as $parent) {
            $members = array_merge($members, self::getMembers($parent, true));
        }
        return $members ?? [];
    }

    private function getClassNameWithoutNamespace(string $class): string
    {
        $parts = explode('\\', $class);

        return end($parts);
    }

    public function hasClasses(): bool
    {
        return count($this->classes) > 0;
    }

    public function getClasses()
    {
        return $this->classes;
    }
}
