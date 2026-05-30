<?php

namespace App\Controller;

use App\Service\MPC;
use App\Service\System;
use BretRZaun\StatusPage\Check\CallbackCheck;
use BretRZaun\StatusPage\Check\DoctrineConnectionCheck;
use BretRZaun\StatusPage\Check\FileCheck;
use BretRZaun\StatusPage\Result;
use BretRZaun\StatusPage\StatusChecker;
use BretRZaun\StatusPage\StatusCheckerGroup;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatusController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly MPC $mpc,
        private readonly System $system,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    #[Route(path: '/status', name: 'status')]
    public function index(): Response
    {
        $checker = new StatusChecker();

        $audio = new StatusCheckerGroup('Audio');
        $audio->addCheck(new CallbackCheck('MPD Daemon', function (Result $result): void {
            if (!$this->mpc->isMpdRunning()) {
                $result->setError('MPD läuft nicht');
            }
        }));
        $audio->addCheck(new CallbackCheck('MPC', function (Result $result): void {
            $this->system->call('mpc');
        }));
        $checker->addGroup($audio);

        $db = new StatusCheckerGroup('Datenbank');
        $db->addCheck(new DoctrineConnectionCheck('Datenbankverbindung', $this->connection));
        $checker->addGroup($db);

        $logoLocation = $this->parameterBag->get('logo_location');
        assert(is_string($logoLocation));
        $playlistLocation = $this->parameterBag->get('playlist_location');
        assert(is_string($playlistLocation));

        $fs = new StatusCheckerGroup('Dateisystem');
        $fs->addCheck((new FileCheck('Logos-Verzeichnis', $logoLocation))->setWritable());
        $fs->addCheck(new CallbackCheck('Playlist-Verzeichnis', function (Result $result) use ($playlistLocation): void {
            $dir = dirname($playlistLocation);
            if (!is_writable($dir)) {
                $result->setError($dir . ' ist nicht beschreibbar');
            }
        }));
        $checker->addGroup($fs);

        $checker->check();

        $code = $checker->hasErrors() ? Response::HTTP_SERVICE_UNAVAILABLE : Response::HTTP_OK;

        return $this->render('@StatusPage/bootstrap_5.html.twig', [
            'results' => $checker->getResults(),
            'title' => 'PIradio Status',
        ], new Response('', $code));
    }
}
