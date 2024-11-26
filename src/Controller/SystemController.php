<?php
/** @noinspection PhpRouteMissingInspection */
/** @noinspection MissingService */

namespace App\Controller;

use App\Exception\SystemCallException;
use App\Service\MPC;
use App\Service\System;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SystemController extends AbstractController
{

    /**
     * SystemController constructor.
     */
    public function __construct(private readonly System $system, private readonly ParameterBagInterface $parameterBag, private readonly MPC $mpc)
    {
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route(path: '/system', name: 'system_status')]
    public function index(Request $request): Response
    {
        return $this->render(
            'system/index.html.twig',
            [
                'resultMessage' => $request->get('msg', false),
                'current_time' => $this->callSystemCommand("TZ='Europe/Berlin' date"),
                'playlist_file' => $this->callSystemCommand('cat ' . $this->parameterBag->get('playlist_location')),
                'mpc_playlist' => $this->callSystemCommand('mpc playlist'),
                'mpd_status' => $this->callSystemCommand('service mpd status'),
                'mpc_status' => $this->callSystemCommand('mpc'),
            ]
        );
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/system/reset/mpd', name: 'system_reset_mpd')]
    public function resetMpd(): RedirectResponse
    {
        try {
            $message = $this->mpc->startMpd();
            return $this->redirectToRoute('system_status', ['s' => $message]);
        } catch (SystemCallException $e) {
            $message =
                $e->getMessage() . PHP_EOL .
                PHP_EOL .
                implode(PHP_EOL, $e->getOutput());
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return $this->redirectToRoute('system_status', ['e' => $message]);
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
                if (preg_match('~error|failed|fehl~i', (string) $line)) {
                    $line = '<strong class="text-danger">'.$line.'</strong>';
                }
                $line = preg_replace('~(.*)(\Wis\W)(.*)~i', '$1<strong class="text-success">$2</strong>$3', (string) $line);
                $result[$i] = $line;
            }

            // finally implode and return
            return implode('<br />', $result);

        } catch (Exception $e) {
            return '<div class="p-3 mb-2 bg-danger text-white">'.$e->getMessage().'</div>';
        }
    }

}
