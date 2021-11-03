<?php

namespace RC\Test\Middleware;

use RC\Middleware\InvokeMiddleware;
use RC\Test\MiddlewareTestCase;

class InvokeMiddlewareTest extends MiddlewareTestCase
{
    public function testItCallsTheHandlerAndNextMiddleware()
    {
        $handler = $this->createPartialMock(InvokeMiddleware::class, ['__invoke']);

        $message = function() use ($handler, &$message) {
            $handler->__invoke($message);
        };

        $middleware = new InvokeMiddleware();

        $handler->expects($this->once())->method('__invoke')->with($message);

        $middleware->handle($message, $this->getStackMock());
    }
}
