<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Program;
use App\Entity\Episode;
use App\Entity\Season;


class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $program = new Program();
        $program->setTitle('Walking dead');
        $program->setSynopsis('Des zombies envahissent la terre');
        $program->setCategory($this->getReference('category_Action'));
        $manager->persist($program);

        $program1 = new Program();
        $program1->setTitle('Walking dead');
        $program1->setSynopsis('Des zombies envahissent la terre');
        $program1->setCategory($this->getReference('category_Aventure'));
        $manager->persist($program1);

        $season = new Season();
        $season->setNumber(1);
        $season->setYear(1990);
        $season->setDescription("description season 1");
        $season->setProgrammId($program);

        $manager->persist($season);

        $episode = new Episode();
        $episode->setTitle('Title Episode 1');
        $episode->setNumber(1);
        $episode->setSynopsis("Synopsis Episode 1");
        $episode->setSeasonId($season);

        $episode2 = new Episode();
        $episode2->setTitle('Title Episode 2');
        $episode2->setNumber(2);
        $episode2->setSynopsis("Synopsis Episode 2");
        $episode2->setSeasonId($season);

        $manager->persist($episode);
        $manager->persist($episode2);

        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            CategoryFixtures::class,
        ];
    }
}
