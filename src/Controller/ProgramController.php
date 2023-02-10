<?php

namespace App\Controller;

use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Form\ProgramType;
use App\Service\ProgramDuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Comment;
use App\Form\Comment1Type;
use App\Form\SearchProgramType;
use App\Repository\CommentRepository;

class ProgramController extends AbstractController
{
    #[Route('/', name: 'program_index')]
    public function index(Request $request, ProgramRepository $programRepository): Response
{
    $form = $this->createForm(SearchProgramType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $search = $form->getData()['search'];
        $programs = $programRepository->findLikeName($search);
    } else {
        $programs = $programRepository->findAll();
    }

    return $this->renderForm('program/index.html.twig', [
        'programs' => $programs,
        'form' => $form,
    ]);
}

    #[Route('/program/new', name: 'program_new')]
    public function new(Request $request, MailerInterface $mailer, ProgramRepository $programRepository, SluggerInterface $slugger): Response
    {
        $program = new Program();

        $form = $this->createForm(ProgramType::class, $program);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $program->setUpdatedAt(date_create_immutable("now"));
            $programRepository->save($program, true);
            $program->setOwner($this->getUser());
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program]));
            $mailer->send($email);
            $this->addFlash('success', 'The new program has been created');

            return $this->redirectToRoute('program_index');

        }

        return $this->renderForm('program/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/program/{slug}/edit', name: 'program_edit')]
    public function editProgram($slug, ProgramRepository $programRepository): Response
    {
        if ($this->getUser() !== $program->getOwner()) {
            // If not the owner, throws a 403 Access Denied exception
            throw $this->createAccessDeniedException('Only the owner can edit the program!');
        }
    }

    #[Route('/show/{slug}', name: 'program_show')]
    public function show($slug, ProgramRepository $programRepository, SeasonRepository $seasonRepository, EpisodeRepository $episodeRepository, ProgramDuration $programDuration): Response
    {
        $program = $programRepository->findOneBy(['slug' => $slug]);
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'programDuration' => $programDuration->calculate($program, $seasonRepository, $episodeRepository),
        ]);
    }

    #[Route('/show/{program}/seasons/{season}', name: 'program_show_season')]
    public function showSeason(Program $program, Season $season, EpisodeRepository $episodeRepository): Response
    {
        $episodes = $episodeRepository->findBy(['season_id' => $season->getId()]);

        return $this->render('seasons/show.html.twig', [
            'season' => $season,
            'program' => $program,
            'episodes' => $episodes,
        ]);
    }

    #[Route('/show/{program_id}/seasons/{season_id}/episode/{episode_id}', name: 'program_episode_show')]
    #[Entity('program', options: ['mapping' => ['program_id' => 'id']])]
    #[Entity('season', options: ['mapping' => ['season_id' => 'id']])]
    #[Entity('episode', options: ['mapping' => ['episode_id' => 'id']])]
    public function showEpisode(Program $program, Season $season, Episode $episode): Response
    {

        return $this->render('episodes/show.html.twig', [
            'season' => $season,
            'program' => $program,
            'episode' => $episode,
        ]);
    }

    #[Route('/show/{program_id}/seasons/{season_id}/episode/{episode_id}/comment', name: 'app_comment_index', methods: ['GET'])]
    #[Entity('program', options: ['mapping' => ['program_id' => 'id']])]
    #[Entity('season', options: ['mapping' => ['season_id' => 'id']])]
    #[Entity('episode', options: ['mapping' => ['episode_id' => 'id']])]
    public function indexComment(Program $program, Season $season, Episode $episode, Request $request, CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(['episode_id' => $episode->getId()]);

        return $this->render(
            'comment/index.html.twig',[
                'comments' => $comments,
                'season' => $season,
                'program' => $program,
                'episode' => $episode,
            ]
            
        );
    }

    #[Route('/show/{program_id}/seasons/{season_id}/episode/{episode_id}/comment/{comment_id}', name: 'app_comment_show', methods: ['GET'])]
    #[Entity('program', options: ['mapping' => ['program_id' => 'id']])]
    #[Entity('season', options: ['mapping' => ['season_id' => 'id']])]
    #[Entity('episode', options: ['mapping' => ['episode_id' => 'id']])]
    public function showComment(Program $program, Season $season, Comment $comment, Episode $episode, Request $request, CommentRepository $commentRepository, EpisodeRepository $episodeRepository): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
            'season' => $season,
            'program' => $program,
            'episode' => $episode,
        ]);
    }

    #[Route('/show/{program_id}/seasons/{season_id}/episode/{episode_id}/comment/{comment_id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    #[Entity('program', options: ['mapping' => ['program_id' => 'id']])]
    #[Entity('season', options: ['mapping' => ['season_id' => 'id']])]
    #[Entity('episode', options: ['mapping' => ['episode_id' => 'id']])]
    public function editComment (Program $program, Season $season, Comment $comment, Episode $episode, Request $request, CommentRepository $commentRepository, EpisodeRepository $episodeRepository): Response
    {
        $form = $this->createForm(Comment1Type::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $comment->setUserId($user);
            $comment->setEpisodeId($episode);
            $commentRepository->save($comment, true);

            return $this->redirectToRoute('app_comment_index', ['season_id' => $season->getId(),
            'program_id' => $program->getId(),
            'episode_id' => $episode->getId(),], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'season' => $season,
            'program' => $program,
            'episode' => $episode,
        ]);
    }

    #[Route('/show/{program_id}/seasons/{season_id}/episode/{episode_id}/comment/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    #[Entity('program', options: ['mapping' => ['program_id' => 'id']])]
    #[Entity('season', options: ['mapping' => ['season_id' => 'id']])]
    #[Entity('episode', options: ['mapping' => ['episode_id' => 'id']])]
    public function newComment(Program $program, Season $season, Episode $episode, Request $request, CommentRepository $commentRepository, EpisodeRepository $episodeRepository): Response
    {
        $comment = new Comment();
        $form = $this->createForm(Comment1Type::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $comment->setUserId($user);
            $comment->setEpisodeId($episode);
            $commentRepository->save($comment, true);

            return $this->redirectToRoute('program_index', [
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'season' => $season,
            'program' => $program,
            'episode' => $episode,
        ]);
    }
}
