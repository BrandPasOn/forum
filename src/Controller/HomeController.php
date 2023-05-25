<?php

namespace App\Controller;

use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $em;
    private $subRepo;

    public function __construct(SubjectRepository $subRepo)
    {
        $this->subRepo = $subRepo;
    }

    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $subjects = $this->subRepo->findAll();


        return $this->render('home/index.html.twig', [
            'subjects' => $subjects,
        ]);
    }
}
