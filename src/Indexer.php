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

    public static function setMember($class, $method, $lineNumber, $type)
    {
        self::$classes[$class]['members'][] = [
            'name' => $method,
            'type' => $type,
            'start_line' => $lineNumber,
        ];
    }

    public static function getMembers(string $class, bool $appendName = false): array
    {
        $methods = self::$classes[$class]['members'] ?? [];

        foreach ($methods as $index => $method) {
            $nameInPath = $appendName ? sprintf('[%s] ', self::getClassNameWithoutNamespace($class)) : '';
            $nameInPath .= sprintf('[%s] ', $method['type']).$method['name'];
            $methods[$index] = [
                'name' => $method['name'],
                'path' => self::$classes[$class]['path'].':'. $method['start_line'].':'.$nameInPath,
            ];
        }

        foreach (self::$classes[$class]['inheritance'] ?? [] as $parent) {
            $methods = array_merge($methods, self::getMembers($parent, true));
        }
        return $methods ?? [];
    }

    public static function getClassNameWithoutNamespace(string $class): string
    {
        $parts = explode('\\', $class);
        return end($parts);
    }
}