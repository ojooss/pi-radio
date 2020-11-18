<?php

namespace App\Controller;

use App\Entity\Station;
use App\Exception\MpcException;
use App\Exception\SystemCallException;
use App\Form\Type\StationFormType;
use App\Service\FileService;
use App\Service\MPC;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;


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
     * @Route("/stations", name="stations")
     *
     * @param Request $request
     * @return Response
     * @throws SystemCallException
     * @throws Exception
     */
    public function index(Request $request)
    {
        /// Init
        $parameter = [
            'logoPath' => $this->parameterBag->get('logo_url_path'),
        ];

        /// StationForm
        $station = new Station();
        $form = $this->createForm(
            StationFormType::class,
            $station,
            [
                //'action' => $this->generateUrl('stations'),
                'method' => 'POST',
            ]
        );
        $form->handleRequest($request);
        $parameter['add_station_form'] = $form->createView();
        if ($form->isSubmitted() && $form->isValid()) {
            $station = $form->getData();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('logo')->getData();
            if ($uploadedFile) {
                $this->fileService->addLogoToStation($uploadedFile, $station);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($station);
            $entityManager->flush();
        }

        /// StationList
        $repository = $this->entityManager->getRepository(Station::class);
        $parameter['stations'] = $repository->findBy([], ['sequenceNr' => 'ASC', 'name' => 'ASC']);

        /// MpcError
        $mpcError = $this->mpc->getError();
        if ($mpcError) {
            $parameter['errorMessage'] = $mpcError;
        }

        /// finally RENDER
        return $this->render(
            'station/index.html.twig',
            $parameter
        );
    }

    /**
     * @Route("/station/{id}/play", name="station_play")
     * @param int $id
     * @return Response
     */
    public function play(int $id)
    {
        $repository = $this->entityManager->getRepository(Station::class);
        $station = $repository->find($id);
        if (null === $station) {
            throw new NotFoundHttpException();
        }

        try {
            $this->mpc->play($station);
        } catch (Throwable $e) {
            return $this->redirectToRoute('index', ['e' => $e->getMessage()]);
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/station/stop", name="station_stop")
     *
     * @return RedirectResponse
     * @throws MpcException
     * @throws SystemCallException
     */
    public function playerStop()
    {
        $this->mpc->stop();

        return $this->redirectToRoute('stations');
    }

    /**
     * @Route("/station/{id}/delete", name="station_delete")
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $repository = $this->entityManager->getRepository(Station::class);
        $station = $repository->find($id);
        if ($station) {
            $this->entityManager->remove($station);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('stations');
    }

}
