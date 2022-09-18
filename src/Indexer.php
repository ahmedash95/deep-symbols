<?php

namespace DeepSymbols;

class Indexer
{
    static array $classes = [];

    public static function setClass($class, $path)
    {
        if (!isset(self::$classes[$class])) {
            self::$classes[$class] = [
                'path' => $path,
                'members' => [],
                'inheritance' => [],
            ];
        }
    }

    public static function setInheritance($class, $parent)
    {
        self::$classes[$class]['inheritance'][] = $parent;
    }

    public static function setMember($class, $method, $lineNumber)
    {
        self::$classes[$class]['members'][] = [
            'name' => $method,
            'start_line' => $lineNumber,
        ];
    }

    public static function getMembers(string $class): array
    {
        $methods = self::$classes[$class]['members'] ?? [];

        foreach ($methods as $index => $method) {
            $methods[$index] = [
                'name' => $method['name'],
                'path' => self::$classes[$class]['path'].':'. $method['start_line'].':'.$method['name'],
            ];
        }

        foreach (self::$classes[$class]['inheritance'] ?? [] as $parent) {
            $methods = array_merge($methods, self::getMembers($parent));
        }
        return $methods ?? [];
    }
}