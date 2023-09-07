<?php
/** @noinspection PhpRouteMissingInspection */
/** @noinspection MissingService */

namespace App\Controller;

use App\Entity\Station;
use App\Exception\MpcException;
use App\Exception\SystemCallException;
use App\Repository\StationRepository;
use App\Service\MPC;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{

    /**
     * IndexController constructor.
     * @param Mpc $mpc
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(private readonly Mpc $mpc, private readonly EntityManagerInterface $entityManager, private readonly ParameterBagInterface $parameterBag)
    {
    }

    /**
     * @Route("/", name="index")
     * @return RedirectResponse|Response
     */
    public function index(): RedirectResponse|Response
    {
        /** @var StationRepository $repository */
        $repository = $this->entityManager->getRepository(Station::class);

        try {
            if ($this->mpc->isPlaying()) {
                return $this->render(
                    'index/index.html.twig',
                    [
                        'playerState' => $this->mpc->getState(),
                        'playerVolume' => $this->mpc->getVolume(),
                        'playerError' => '', // $this->mpc->getError(),
                        'station' => $repository->getCurrent(),
                        'logoPath' => $this->parameterBag->get('logo_url_path'),
                    ]
                );

            } else {
                return $this->redirectToRoute('stations');
            }
        } catch (Exception $e) {
            return $this->redirectToRoute('stations', ['e' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/volume/mute", name="volume_mute")
     *
     * @return RedirectResponse|Response
     * @throws MpcException
     * @throws SystemCallException
     */
    public function volumeMute(): RedirectResponse|Response
    {
        $this->mpc->setVolume(0);

        return $this->index();
    }

    /**
     * @Route("/volume/down", name="volume_down")
     *
     * @return RedirectResponse|Response
     * @throws MpcException
     * @throws SystemCallException
     */
    public function volumeDown(): RedirectResponse|Response
    {
        $volume = $this->mpc->getVolume();
        $volume -=5;
        if ($volume < 0) {
            $volume = 0;
        }
        $this->mpc->setVolume($volume);

        return $this->index();
    }

    /**
     * @Route("/volume/up", name="volume_up")
     *
     * @return RedirectResponse|Response
     * @throws MpcException
     * @throws SystemCallException
     */
    public function volumeUp(): RedirectResponse|Response
    {
        $volume = $this->mpc->getVolume();
        $volume +=5;
        if ($volume > 100) {
            $volume = 100;
        }
        $this->mpc->setVolume($volume);

        return $this->index();
    }

    /**
     * @Route("/volume/full", name="volume_full")
     *
     * @return RedirectResponse|Response
     * @throws MpcException
     * @throws SystemCallException
     */
    public function volumeFull(): RedirectResponse|Response
    {
        $this->mpc->setVolume(100);

        return $this->index();
    }


    /**
     * @Route("/volume/{value<\d+>}", name="volume_set")
     *
     * @param $value
     * @return RedirectResponse|Response
     * @throws MpcException
     * @throws SystemCallException
     */
    public function volumeSet($value): RedirectResponse|Response
    {
        if ($value >= 0 && $value <= 100) {
            $this->mpc->setVolume((int)$value);
        }

        return $this->index();
    }
}
