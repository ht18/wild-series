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
        $program1->setTitle('Walking dead 2');
        $program1->setSynopsis('Des zombies envahissent la terre');
        $program1->setCategory($this->getReference('category_Aventure'));
        $manager->persist($program1);

        $this->addReference('program_' . 1, $program);
        $this->addReference('program_' . 2, $program1);

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
