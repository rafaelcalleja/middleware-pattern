<?php

declare(strict_types=1);

namespace RC;

class Pipeline
{
    private iterable $middlewareAggregate;

    /**
     * @param Middleware[]|iterable $middlewareHandlers
     */
    public function __construct(iterable|\IteratorAggregate $middlewareHandlers = [])
    {
        if ($middlewareHandlers instanceof \IteratorAggregate) {
            $this->middlewareAggregate = $middlewareHandlers;
        } elseif (\is_array($middlewareHandlers)) {
            $this->middlewareAggregate = new \ArrayObject($middlewareHandlers);
        } else {
            // $this->middlewareAggregate should be an instance of IteratorAggregate.
            // When $middlewareHandlers is an Iterator, we wrap it to ensure it is lazy-loaded and can be rewound.
            $this->middlewareAggregate = new class($middlewareHandlers) implements \IteratorAggregate {
                private $middlewareHandlers;
                private $cachedIterator;

                public function __construct(\Traversable $middlewareHandlers)
                {
                    $this->middlewareHandlers = $middlewareHandlers;
                }

                public function getIterator(): \Traversable
                {
                    if (null === $this->cachedIterator) {
                        $this->cachedIterator = new \ArrayObject(iterator_to_array($this->middlewareHandlers, false));
                    }

                    return $this->cachedIterator;
                }
            };
        }
    }

    public function __invoke(callable $message)
    {
        $middlewareIterator = $this->middlewareAggregate->getIterator();

        while ($middlewareIterator instanceof \IteratorAggregate) {
            $middlewareIterator = $middlewareIterator->getIterator();
        }

        $middlewareIterator->rewind();

        if (false === $middlewareIterator->valid()) {
            return $message;
        }

        $stack = new DefaultStackMiddleware($middlewareIterator);

        return $middlewareIterator->current()->handle($message, $stack);
    }
}
