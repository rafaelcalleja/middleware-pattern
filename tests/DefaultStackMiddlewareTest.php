<?php

namespace RC\Test;

use PHPUnit\Framework\TestCase;
use RC\Pipeline;
use RC\Middleware;
use RC\Stack;

class DefaultStackMiddlewareTest extends TestCase
{
    public function testClone()
    {
        $middleware1 = $this->createMock(Middleware::class);
        $middleware1
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (callable $message, Stack $stack): callable {
                $fork = clone $stack;

                $stack->next()->handle($message, $stack);
                $fork->next()->handle($message, $fork);

                return $message;
            })
        ;

        $middleware2 = $this->createMock(Middleware::class);
        $middleware2
            ->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function (callable $message, Stack $stack): callable {
                return $message;
            })
        ;

        $client = new Pipeline([$middleware1, $middleware2]);

        $client->__invoke(static fn () => null);
    }
}
