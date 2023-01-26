<?php

namespace App\Service;

use App\Entity\Program;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;

class ProgramDuration
{
    public function calculate(Program $program, SeasonRepository $seasonRepository, EpisodeRepository $episodeRepository): string
    {
        $seasons = $seasonRepository->findBy(['programm_id' => $program->getId()]);
        $result = 0;
        $seasonsNbr = [];
        foreach ($seasons as $season){
            array_push($seasonsNbr, $season->getId());
        }
        for ($i = 0; $i < count($seasonsNbr); ++$i ){
            $episodes = $episodeRepository->findBy(['season_id' => $seasonsNbr[$i]]);
            foreach ($episodes as $episode){
                $result = $result + $episode->getDuration();
            }
        }
        return $result;
    }
}


?>