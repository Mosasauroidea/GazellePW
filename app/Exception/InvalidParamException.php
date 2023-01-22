<?php

namespace Gazelle\Exception;

use Throwable;

class InvalidParamException extends \RuntimeException {
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct("Invalid param: $message", $code, $previous);
    }
}
