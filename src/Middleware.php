<?php

declare(strict_types=1);

namespace RC;

interface Middleware
{
    public function handle(callable $operation, Stack $stack);
}
