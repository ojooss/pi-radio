<?php


namespace App\Exception;


use Throwable;


class SystemCallException extends \Exception {

    /**
     * @var array
     */
    private array $output;

    /**
     * SystemCallException constructor.
     * @param string $message
     * @param array $output
     * @param int $code
     */
    public function __construct($message = "", $output = [], $code = 0)
    {
        $this->output = $output;
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
