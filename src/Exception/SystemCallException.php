<?php


namespace App\Exception;


use Exception;


class SystemCallException extends Exception {

    /**
     * SystemCallException constructor.
     * @param string $message
     * @param array<string> $output
     * @param int $code
     */
    public function __construct(string $message = "", private readonly array $output = [], int $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * @return array<string>
     */
    public function getOutput(): array
    {
        return $this->output;
    }

}
