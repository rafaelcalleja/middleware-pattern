<?php

declare(strict_types=1);

namespace RC;

class MiddlewareStack
{
    public ?iterable $iterator;

    public array $stack = [];

    public function next(int $offset): ?Middleware
    {
        if (isset($this->stack[$offset])) {
            return $this->stack[$offset];
        }

        if (null === $this->iterator) {
            return null;
        }

        $this->iterator->next();

        if (!$this->iterator->valid()) {
            return $this->iterator = null;
        }

        return $this->stack[] = $this->iterator->current();
    }
}
