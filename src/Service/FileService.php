<?php

namespace App\Service;

use App\Entity\Station;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class FileService
{

    /**
     * @var mixed
     */
    private $logoDir;

    /**
     * @var mixed
     */
    private $logoUrlPath;

    /**
     * FileService constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->logoDir  = $parameterBag->get('logo_location');
        $this->logoUrlPath = $parameterBag->get('logo_url_path');
    }

    /**
     * @return mixed
     */
    public function getLogoDir()
    {
        return $this->logoDir;
    }

    /**
     * @param mixed $logoDir
     * @return FileService
     */
    public function setLogoDir($logoDir)
    {
        $this->logoDir = $logoDir;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogoUrlPath()
    {
        return $this->logoUrlPath;
    }

    /**
     * @param mixed $logoUrlPath
     * @return FileService
     */
    public function setLogoUrlPath($logoUrlPath)
    {
        $this->logoUrlPath = $logoUrlPath;
        return $this;
    }

    /**
     * @param File $file
     * @param Station $station
     * @param bool $copy
     * @throws Exception
     */
    public function addLogoToStation(File $file, Station $station, $copy=false)
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
     * @param Station $station
     * @throws Exception
     */
    public function removeLogoFromStation(Station $station)
    {
        if (file_exists($this->logoDir . '/' . $station->getLogoName())) {
            if (false === unlink($this->logoDir . '/' . $station->getLogoName())) {
                throw new Exception('can not remove logo file');
            }
        }
        $station->setLogoName('deleted');
    }

}
