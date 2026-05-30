<?php

namespace App\Tests\Service;

use Override;
use App\Entity\Station;
use App\Service\FileService;
use App\Tests\PHPUnitUtils;
use Exception;
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

    /**
     * @throws Exception
     */
    #[Override]
    final function setUp(): void
    {
        static::bootKernel();

        $fileService = self::getContainer()->get(FileService::class);
        assert($fileService instanceof FileService);
        $this->fileService = $fileService;

        if (!file_exists(__DIR__.'/../../var/test')) {
            if (false === mkdir(__DIR__ . '/../../var/test', 0775, true)) {
                throw new Exception('Can not create test tmp dir');
            }
        }

        $this->fileService->setLogoDir(__DIR__.'/../../var/test');
        $this->filePath = __DIR__.'/../Data/'.uniqid('test').'.png';
        if (false === copy(__DIR__.'/../Data/TestLogo.png', $this->filePath)) {
            throw new Exception('Can not create test file copy');
        }
    }

    /**
     * @throws ReflectionException
     */
    public function testAddLogoToStation(): void
    {
        $file = new File($this->filePath);
        $station = new Station();
        $this->callPrivateMethod($this->fileService, 'addLogoToStation', [$file, $station]);

        $expected = $this->fileService->getLogoDir() . '/' . $station->getLogoName();
        self::assertEquals(basename($expected), $station->getLogoName());
        self::assertTrue(file_exists($expected));
    }

    /**
     * @throws ReflectionException
     */
    public function testRemoveLogoFromStation(): void
    {
        $file = new File($this->filePath);
        $station = new Station();
        $this->callPrivateMethod($this->fileService, 'addLogoToStation', [$file, $station]);

        $expected = $this->fileService->getLogoDir() . '/' . $station->getLogoName();
        self::assertEquals(basename($expected), $station->getLogoName());
        self::assertTrue(file_exists($expected));

        $this->callPrivateMethod($this->fileService, 'removeLogoFromStation', [$station]);
        self::assertFalse(file_exists($expected));
    }

}
