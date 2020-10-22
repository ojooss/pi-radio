<?php

namespace App\Controller;

use App\Exception\SystemCallException;
use App\Service\MPC;
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

        if ($this->mpc->isPlaying()) {
            $status = $this->mpc->getCurrent();
        } else {
            return $this->redirectToRoute('stations');
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
