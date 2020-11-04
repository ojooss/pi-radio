<?php

namespace App\Controller;

use App\Exception\SystemCallException;
use App\Service\MPC;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{

    /**
     * @var Mpc
     */
    private Mpc $mpc;

    /**
     * IndexController constructor.
     * @param Mpc $mpc
     */
    public function __construct(MPC $mpc)
    {
        $this->mpc = $mpc;
    }

    /**
     * @Route("/", name="index")
     * @throws SystemCallException
     */
    public function index()
    {

        try {
            if ($this->mpc->isPlaying()) {
                $status = $this->mpc->getCurrent();
            } else {
                return $this->redirectToRoute('stations');
            }
        } catch (Exception $e) {
            return $this->redirectToRoute('stations', ['e' => $e->getMessage()]);
        }

        return $this->render(
            'index/index.html.twig',
            [
                'playerStatus' => $status,
                'playerError' => $this->mpc->getError(),
            ]
        );

    }

}
