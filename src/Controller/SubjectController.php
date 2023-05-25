<?php

namespace App\Controller;

use DateTimeZone;
use App\Entity\Tag;
use App\Entity\Comment;
use App\Entity\Subject;
use App\Entity\Vote;
use App\Form\CommentType;
use App\Form\SubjectType;
use App\Form\VoteType;
use App\Repository\CommentRepository;
use App\Repository\TagRepository;
use App\Repository\SubjectRepository;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubjectController extends AbstractController
{
    private $entityManager;
    private $repo;
    private $voteRepo;
    private $commentRepo;

    public function __construct(EntityManagerInterface $entityManager, SubjectRepository $repo, VoteRepository $voteRepo, CommentRepository $commentRepo)
    {
        $this->entityManager = $entityManager;
        $this->repo = $repo;
        $this->voteRepo = $voteRepo;
        $this->commentRepo = $commentRepo;
    }

    #[Route('/subject/new', name: 'app_subject_new')]
    public function index(Request $request, TagRepository $tagRepo): Response
    {
        $subject = new Subject();
        $comment = new Comment();

        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $subject->setCreatedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
            $subject->setUser($this->getUser());

            if (isset($request->get('subject')['use_tags'])) {
                foreach ($request->get('subject')['use_tags'] as $useTag) {
                    $subject->addTag($tagRepo->find($useTag));
                }
            }

            $comment->setContent($request->get('subject')['comment']['content']);
            $comment->setCreatedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
            $comment->setEditedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
            $comment->setUser($this->getUser());

            $subject->addComment($comment);

            $this->entityManager->persist($subject);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            if (isset($request->get('subject')['tags'])) {
                foreach ($request->get('subject')['tags'] as $tag) {

                    $newTag = $tagRepo->findByName($tag['name']);
                    $newTag->addSubject($subject);
                    
                    $this->entityManager->persist($newTag);
                }
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_subject', ['id' => $subject->getId()]);
        }

        return $this->render('subject/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/subject/{id}', name: 'app_subject')]
    public function show(Subject $subject, Request $request)
    {
        
        $vote = new Vote();

        $voteForm = $this->createform(VoteType::class, $vote);
        $voteForm->handleRequest($request);

        if ($voteForm->isSubmitted() && $voteForm->isValid()) {
            
            if ($voteForm->get('upVote')->getName() === 'upVote') {
                $vote->setIsUp(true); 
            }  elseif ($voteForm->get('downVote')->getName() === 'downVote') {
                $vote->setIsUp(false);
            }

            $vote->setUser($this->getUser());
            $subject->addVote($vote);
            
            $this->entityManager->persist($subject);
            $this->entityManager->persist($vote);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_subject', ['id' => $subject->getId()]);
        }
        
        $comment = new Comment();
        $commentForm = $this->createform(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            $comment->setCreatedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
            $comment->setEditedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
            $comment->setUser($this->getUser());

            $subject->addComment($comment);

            $this->entityManager->persist($subject);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_subject', ['id' => $subject->getId()]);
        }

        return $this->render('subject/show.html.twig', [
            'subject' => $subject,
            'commentForm' => $commentForm->createView(),
            'voteForm' => $voteForm->createView()
        ]);
    }
}
