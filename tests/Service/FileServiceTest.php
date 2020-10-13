<?php

namespace App\Tests\Service;

use App\Entity\Station;
use App\Service\FileService;
use App\Tests\PHPUnitUtils;
use PHPUnit\Runner\Exception;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

class FileServiceTest extends KernelTestCase {

    use PHPUnitUtils;

    /**
     * @var FileService
     */
    private FileService $fileService;

    /**
     * @var string
     */
    private string $filePath;

    final function setUp(): void
    {
        static::bootKernel();

        $this->fileService = self::$container->get(FileService::class);

        if (!file_exists(__DIR__.'/../../var/test')) {
            if (false === mkdir(__DIR__ . '/../../var/test', 0775, true)) {
                throw new Exception('Can not create test tmp dir');
            }
        }

        $this->fileService->setLogoDir(__DIR__.'/../../var/test');
        $this->filePath = __DIR__.'/../Data/'.uniqid('test').'.png';
        if (false === copy(__DIR__.'/../Data/TestLogo.png', $this->filePath)) {
            throw new \Exception('Can not create test file copy');
        }
    }

    /**
     * @throws ReflectionException
     */
    public function testAddLogoToStation()
    {
        $file = new File($this->filePath);
        $station = new Station();
        $this->callPrivateMethod($station, 'setId', [42]);
        $this->callPrivateMethod($this->fileService, 'addLogoToStation', [$file, $station]);

        $expected = $this->fileService->getLogoDir() . '/' . $station->getLogoName();
        $this->assertEquals(basename($expected), $station->getLogoName());
        $this->assertTrue(file_exists($expected));
    }

    /**
     * @throws ReflectionException
     */
    public function testRemoveLogoFromStation()
    {
        $file = new File($this->filePath);
        $station = new Station();
        $this->callPrivateMethod($station, 'setId', [42]);
        $this->callPrivateMethod($this->fileService, 'addLogoToStation', [$file, $station]);

        $expected = $this->fileService->getLogoDir() . '/' . $station->getLogoName();
        $this->assertEquals(basename($expected), $station->getLogoName());
        $this->assertTrue(file_exists($expected));

        $this->callPrivateMethod($this->fileService, 'removeLogoFromStation', [$station]);
        $this->assertFalse(file_exists($expected));
    }

}
