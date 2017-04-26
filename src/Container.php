<?php


namespace Studiow\Autowire;


use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * Map callbacks to interface names
     * @var array
     */
    private $callbacks = [];

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * Set the wrapped container
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get the wrapped container
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }


    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        $entry = $this->getContainer()->get($id);
        if (is_object($entry)) {
            $callbacks = $this->getCallbacks($entry);
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, [$entry, $this]);
            }
        }
        return $entry;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->getContainer()->has($id);
    }

    public function attach(string $interfaceName, callable ... $callbacks)
    {
        foreach ($callbacks as $callback) {
            $this->callbacks[$interfaceName][] = $callback;
        }
    }

    private function getCallbacks($entry): array
    {
        $interfaces = class_implements($entry);

        return array_reduce($interfaces, function ($carry, $interfaceName) {
            if (array_key_exists($interfaceName, $this->callbacks)) {
                $carry = array_merge($carry, $this->callbacks[$interfaceName]);
            }
            return $carry;
        }, []);

    }
}