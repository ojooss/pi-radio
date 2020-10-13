<?php

namespace App\Controller;

use App\Entity\Station;
use App\Repository\StationRepository;
use App\Service\FileService;
use App\Service\MPC;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


class StationController extends AbstractController
{

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var MPC
     */
    private MPC $mpc;
    /**
     * @var FileService
     */
    private FileService $fileService;
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * StationController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param MPC $mpc
     * @param FileService $fileService
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $entityManager, MPC $mpc, FileService $fileService, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->mpc = $mpc;
        $this->fileService = $fileService;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/station", name="station")
     */
    public function index()
    {
        /** @var StationRepository $repository */
        $repository = $this->entityManager->getRepository(Station::class);

        return $this->render(
            'station/index.html.twig',
            [
                'stations' => $repository->findAll(),
                'logoPath' => $this->parameterBag->get('logo_url_path'),
            ]
        );
    }

    /**
     * @Route("/station/{id}/play", name="station_play")
     * @param int $id
     * @return RedirectResponse
     */
    public function play(int $id)
    {
        /** @var StationRepository $repository */
        $repository = $this->entityManager->getRepository(Station::class);
        $station = $repository->find($id);
        if (null === $station) {
            throw new NotFoundHttpException();
        }

        $this->mpc->play($station);

        return $this->redirectToRoute('index');
    }


}
