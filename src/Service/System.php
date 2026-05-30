<?php


namespace App\Service;


use App\Exception\SystemCallException;


class System
{

    /**
     * @param string $command
     * @return array<string>
     * @throws SystemCallException
     */
    public function call(string $command): array
    {
        $output = [];
        $ret = 0;
        exec($command, $output, $ret);
        if ($ret != 0) {
            throw new SystemCallException('command "'.$command.'" failed', $output);
        }
        return $output;
    }

    /**
     * @param array<string> $stdOut
     * @param string $validationRegex
     * @param string|null $exceptionClass
     * @param string|null $exceptionMessage
     * @return bool
     */
    public function validateOutput(array $stdOut, string $validationRegex, ?string $exceptionClass = null, ?string $exceptionMessage = null): bool
    {
        if (empty(
            array_filter($stdOut, function ($element) use ($validationRegex) {
                return (bool) preg_match('~'.$validationRegex.'~', $element);
            })
        )) {
            if (null !== $exceptionClass && is_a($exceptionClass, \Throwable::class, true)) {
                throw new $exceptionClass($exceptionMessage);
            }
            else {
                return false;
            }
        }
        return true;
    }

}
