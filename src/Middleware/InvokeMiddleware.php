<?php

declare(strict_types=1);

namespace RC\Middleware;

use RC\Middleware;
use RC\Stack;

class InvokeMiddleware implements Middleware
{
    public function handle(callable $operation, Stack $stack)
    {
        try {
            call_user_func($operation);
        } finally {
            return $stack->next()->handle($operation, $stack);
        }
    }
}