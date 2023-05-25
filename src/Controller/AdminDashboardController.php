<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\SubjectRepository;
use App\Repository\UserRepository;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    private $em;
    private $userRepo;
    private $subjectRepo;
    private $commentRepo;
    private $voteRepo;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo, SubjectRepository $subjectRepo, CommentRepository $commentRepo, VoteRepository $voteRepo)
    {
        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->subjectRepo = $subjectRepo;
        $this->commentRepo = $commentRepo;
        $this->voteRepo = $voteRepo;
    }

    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        $users = $this->userRepo->findBy([], ['created_at' => 'DESC'], 3);

        $usersCount = $this->userRepo->findAll();
        $subjects = $this->subjectRepo->findAll();
        $comments = $this->commentRepo->findAll();
        $votes = $this->voteRepo->findAll();

        return $this->render('admin_dashboard/index.html.twig', [
            'users' => $users,
            'usersCount' => $usersCount,
            'subjects' => $subjects,
            'comments' => $comments,
            'votes' => $votes,
        ]);
    }
}
