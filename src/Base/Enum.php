<?php

namespace Kfn\Base;

use ReflectionClass;

abstract class Enum
{
    public string $name;
    public mixed $value;

    private static ReflectionClass|null $instance = null;

    public function __construct(string $name)
    {
        static::getInstance();
        if (static::$instance->hasConstant($name)) {
            $this->name = $name;
            $this->value = static::{$name};
        }
    }

    public static function cases(): array
    {
        static::getInstance();
        return array_map(fn($e) => static::tryFrom($e), array_keys(static::$instance->getConstants()));
    }

    public static function tryFrom(string $name): static|null
    {
        static::getInstance();
        if (static::$instance->hasConstant($name)) {
            return new static($name);
        }
        return null;
    }

    private static function getInstance(): void
    {
        if (! static::$instance instanceof ReflectionClass) {
            static::$instance = new ReflectionClass(static::class);
        }
    }

    public static function __callStatic(string $name, array $args = []): static|null
    {
        return static::tryFrom($name);
    }
}
