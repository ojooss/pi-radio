<?php

namespace App\Controller;

use App\Service\System;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class SystemController extends AbstractController
{

    /**
     * @var System
     */
    private System $system;

    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * SystemController constructor.
     * @param System $system
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(System $system, ParameterBagInterface $parameterBag)
    {
        $this->system = $system;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/system", name="system")
     */
    public function index()
    {
        return $this->render(
            'system/index.html.twig',
            [
                'current_time' => $this->callSystemCommand("TZ='Europe/Berlin' date"),
                'playlist_file' => $this->callSystemCommand('cat ' . $this->parameterBag->get('playlist_location')),
                'mpc_playlist' => $this->callSystemCommand('mpc playlist'),
                'mpd_status' => $this->callSystemCommand('service mpd status'),
                'mpc_status' => $this->callSystemCommand('mpc'),
            ]
        );
    }

    /**
     * @param string $cmd
     * @return string
     */
    protected function callSystemCommand(string $cmd): string
    {
        try {
            // call system
            $result = $this->system->call($cmd);

            // highlight some pattern
            foreach ($result as $i => $line) {
                if (preg_match('~error|failed|fehl~i', $line)) {
                    $line = '<strong class="text-danger">'.$line.'</strong>';
                }
                $line = preg_replace('~(.*)(\Wis\W)(.*)~i', '$1<strong class="text-success">$2</strong>$3', $line);
                $result[$i] = $line;
            }

            // finally implode and return
            return implode('<br />', $result);

        } catch (Exception $e) {
            return '<div class="p-3 mb-2 bg-danger text-white">'.$e->getMessage().'</div>';
        }
    }

}