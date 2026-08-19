<?php

declare(strict_types=1);

namespace Tests\Http;

use PHPUnit\Framework\TestCase;
use App\Http\Container;

class DummyDependency {}

class DummyClassWithDefaultParam
{
    public function __construct(
        public DummyDependency $dep,
        public array $config = ['default' => true]
    ) {}
}

class ContainerTest extends TestCase
{
    public function testContainerResolvesClassWithDefaultParameter(): void
    {
        $container = new Container();
        /** @var DummyClassWithDefaultParam $instance */
        $instance = $container->get(DummyClassWithDefaultParam::class);

        $this->assertInstanceOf(DummyClassWithDefaultParam::class, $instance);
        $this->assertInstanceOf(DummyDependency::class, $instance->dep);
        $this->assertEquals(['default' => true], $instance->config);
    }
}
