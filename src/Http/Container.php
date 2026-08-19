<?php

declare(strict_types=1);

namespace App\Http;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    private array $instances = [];

    public function set(string $class, object $instance): void
    {
        $this->instances[$class] = $instance;
    }

    public function get(string $class): object
    {
        // Retorna instância já criada
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(
                "A classe {$class} não pode ser instanciada."
            );
        }

        $constructor = $reflection->getConstructor();

        // Classe sem construtor
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            $instance = new $class();

            $this->instances[$class] = $instance;

            return $instance;
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new RuntimeException(
                    sprintf(
                        'Não foi possível resolver a dependência "$%s" de %s.',
                        $parameter->getName(),
                        $class
                    )
                );
            }

            $dependencies[] = $this->get($type->getName());
        }

        $instance = $reflection->newInstanceArgs($dependencies);

        $this->instances[$class] = $instance;

        return $instance;
    }
}