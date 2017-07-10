<?php

namespace Amp;



/**
 * Creates a failed promise using the given exception.
 */
final class Failure implements Promise {
    /** @var \Throwable $exception */
    private $exception;

    /**
     * @param \Throwable $exception Rejection reason.
     */
    public function __construct(\Throwable $exception) {
        $this->exception = $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function onResolve(callable $onResolved) {
        try {
            $result = $onResolved($this->exception, null);

            if ($result === null) {
                return;
            }

            if ($result instanceof \Generator) {
                $result = new Coroutine($result);
            }

            if ($result instanceof Promise) {
                Promise\rethrow($result);
            }
        } catch (\Throwable $exception) {
            Loop::defer(static function () use ($exception) {
                throw $exception;
            });
        }
    }
}
