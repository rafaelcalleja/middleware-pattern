<?php

declare(strict_types=1);

namespace RC;

interface Stack
{
    /**
     * Returns the next middleware to process.
     */
    public function next(): Middleware;
}
