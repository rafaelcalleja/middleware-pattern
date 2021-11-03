<?php

declare(strict_types=1);

namespace RC;

class DefaultStackMiddleware implements Middleware, Stack
{
	//https://muniftanjim.dev/blog/basic-middleware-pattern-in-javascript/
    private MiddlewareStack $stack;

    private int $offset = 0;

    /**
     * @param iterable|Middleware[]|Middleware|null $middlewareIterator
     */
    public function __construct(\Iterator|Middleware $middlewareIterator = null)
    {
        $this->stack = new MiddlewareStack();

        if (null === $middlewareIterator) {
            return;
        }

        if ($middlewareIterator instanceof \Iterator) {
            $this->stack->iterator = $middlewareIterator;
        } elseif ($middlewareIterator instanceof Middleware) {
            $this->stack->stack[] = $middlewareIterator;
        } elseif (!is_iterable($middlewareIterator)) {
            throw new \TypeError(sprintf('Argument 1 passed to "%s()" must be iterable of "%s", "%s" given.', __METHOD__, Middleware::class, get_debug_type($middlewareIterator)));
        } else {
            $this->stack->iterator = (function () use ($middlewareIterator) {
                yield from $middlewareIterator;
            })();
        }
    }

    public function handle(callable $operation, Stack $stack)
    {
        return $operation;
    }


    public function next(): Middleware
    {
        if (null === $next = $this->stack->next($this->offset)) {
            return $this;
        }

        ++$this->offset;

        return $next;
    }
}
