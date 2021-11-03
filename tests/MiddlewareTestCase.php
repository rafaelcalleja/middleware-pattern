<?php

namespace RC\Test;

use PHPUnit\Framework\TestCase;
use RC\DefaultStackMiddleware;
use RC\Middleware;
use RC\Stack;

abstract class MiddlewareTestCase extends TestCase
{
    protected function getStackMock(bool $nextIsCalled = true)
    {
        if (!$nextIsCalled) {
            $stack = $this->createMock(Stack::class);
            $stack
                ->expects($this->never())
                ->method('next')
            ;

            return $stack;
        }

        $nextMiddleware = $this->createMock(Middleware::class);
        $nextMiddleware
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (callable $message, Stack $stack): callable {
                return $message;
            })
        ;

        return new DefaultStackMiddleware($nextMiddleware);
    }

    protected function getThrowingStackMock(\Throwable $throwable = null)
    {
        $nextMiddleware = $this->createMock(Middleware::class);
        $nextMiddleware
            ->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable ?? new \RuntimeException('Thrown from next middleware.'))
        ;

        return new DefaultStackMiddleware($nextMiddleware);
    }
}