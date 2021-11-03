<?php

namespace RC\Test;

use RC\Middleware;
use RC\Pipeline;
use PHPUnit\Framework\TestCase;
use RC\Stack;

class PipelineTest extends TestCase
{
    public function testPrePostMethod()
    {
        $expectedOutput = [
            'preText_1',
            'preText_2',
            'running',
            'postText_2',
            'postText_1',
        ];

        $logger = new class() {
           private array $output = [];

           public function log(string $text) {
               $this->output[] = $text;
           }

           public function getLog(): array {
               return $this->output;
           }
        };


        $middlewareLogger = function($logger, string $preText, string $postText) {
            return new class($logger, $preText, $postText) implements Middleware {
                private string $preText;
                private string $postText;
                private object $logger;

                public function __construct($logger, string $preText, string $postText)
                {
                    $this->preText = $preText;
                    $this->postText = $postText;
                    $this->logger = $logger;
                }

                public function handle(callable $operation, Stack $stack)
                {
                    try {
                        $this->logger->log($this->preText);
                        $stack->next()->handle($operation, $stack);
                    } finally {
                        $this->logger->log($this->postText);
                    }
                }
            };
        };

        $runner = new Pipeline(
            [
                $middlewareLogger($logger, 'preText_1', 'postText_1'),
                $middlewareLogger($logger, 'preText_2', 'postText_2'),
                new Middleware\InvokeMiddleware(),
            ]
        );

        $runner->__invoke(static fn () => $logger->log('running'));

        $this->assertSame($expectedOutput, $logger->getLog());
    }
}
