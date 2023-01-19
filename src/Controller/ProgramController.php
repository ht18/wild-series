<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;



class ProgramController extends AbstractController
{
    #[Route('/', name: 'program_index')]
    public function index(ProgramRepository $programRepository): Response
    {
        $programs = $programRepository->findAll();

        return $this->render(
            'program/index.html.twig',
            ['programs' => $programs]
        );
    }

    #[Route('/show/{id<^[0-9]+$>}', name: 'program_show')]
    public function show(int $id, ProgramRepository $programRepository): Response
    {
        $program = $programRepository->findOneBy(['id' => $id]);
        // same as $program = $programRepository->find($id);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : ' . $id . ' found in program\'s table.'
            );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
        ]);
    }

    #[Route('/show/{id<^[0-9]+$>}/seasons/{seasonId<^[0-9]+$>}', name: 'program_show_season')]
    public function showSeason(int $id, int $seasonId, EpisodeRepository $episodeRepository, ProgramRepository $programRepository, SeasonRepository $seasonRepository): Response
    {
        $season = $seasonRepository->findOneBy(['id' => $seasonId, 'programm_id' => $id]);
        $program = $programRepository->findOneBy(['id' => $id]);
        $episodes = $episodeRepository->findBy(['season_id' => $seasonId]);

        if (!$season) {
            throw $this->createNotFoundException(
                'No program with id : ' . $seasonId . ' found in program\'s table.'
            );
        }
        return $this->render('season/show.html.twig', [
            'season' => $season,
            'program' => $program,
            'episodes' => $episodes,
        ]);
    }
}
