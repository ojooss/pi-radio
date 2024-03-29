<?php
/** @noinspection PhpUnused */
/** @noinspection MissingService */

namespace App\Service;

use App\Entity\Station;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class FileService
{

    /**
     * @var string
     */
    private string $logoDir;

    /**
     * @var string
     */
    private string $logoUrlPath;

    /**
     * FileService constructor.
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->logoDir  = $parameterBag->get('logo_location');
        $this->logoUrlPath = $parameterBag->get('logo_url_path');
    }

    /**
     * @return string
     */
    public function getLogoDir(): string
    {
        return $this->logoDir;
    }

    /**
     * @param mixed $logoDir
     * @return FileService
     */
    public function setLogoDir(string $logoDir): FileService
    {
        $this->logoDir = $logoDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoUrlPath(): string
    {
        return $this->logoUrlPath;
    }

    /**
     * @param mixed $logoUrlPath
     * @return FileService
     */
    public function setLogoUrlPath(string $logoUrlPath): FileService
    {
        $this->logoUrlPath = $logoUrlPath;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function addLogoToStation(File $file, Station $station, bool $copy=false): void
    {
        $logoName = uniqid() . '.' . $file->getExtension();
        if ($copy) {
            $res = copy($file->getRealPath(), $this->logoDir . '/' . $logoName);
        } else {
            $res = rename($file->getRealPath(), $this->logoDir . '/' . $logoName);
        }
        if ($res) {
            $station->setLogoName($logoName);
        } else {
            throw new Exception('can not '.($copy?'copy':'move').' logo file');
        }
    }

    /**
     * @throws Exception
     */
    public function removeLogoFromStation(Station $station): void
    {
        if (file_exists($this->logoDir . '/' . $station->getLogoName())) {
            if (false === unlink($this->logoDir . '/' . $station->getLogoName())) {
                throw new Exception('can not remove logo file');
            }
        }
        $station->setLogoName('deleted');
    }

}
