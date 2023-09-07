<?php


namespace App\Exception;


use Exception;


class SystemCallException extends Exception {

    /**
     * SystemCallException constructor.
     * @param string $message
     * @param array $output
     * @param int $code
     */
    public function __construct(string $message = "", private readonly array $output = [], $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

}
