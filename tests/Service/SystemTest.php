<?php

namespace App\Tests\Service;

use App\Exception\SystemCallException;
use App\Service\System;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;


class SystemTest extends KernelTestCase
{

    /**
     * @throws SystemCallException
     */
    final function testCall(): void
    {
        $service = new System();
        $result = $service->call('ls -l ' . __DIR__);
        $this->assertIsArray($result);

        try {
            $service->call('/bin/no/valid/command 2>&1');
            $this->fail('Exception not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('command "/bin/no/valid/command 2>&1" failed', $e->getMessage());
        }

    }

    /**
     * @throws Throwable
     */
    final  function testValidateOutput(): void
    {
        $service = new System();
        $result = $service->validateOutput(['some console output'], 'console');
        $this->assertTrue($result);

        $result = $service->validateOutput(['some console output'], 'not present');
        $this->assertFalse($result);

        try {
            $service->validateOutput(['some console output'], 'not present', Exception::class, 'ExceptionMessage');
            $this->fail('Exception not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('ExceptionMessage', $e->getMessage());
        }
    }

}
