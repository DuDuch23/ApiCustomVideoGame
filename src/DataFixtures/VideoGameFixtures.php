<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\VideoGame;
use App\Entity\Category;
use App\Entity\Editor;

class VideoGameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $videoGames = [
            [
                'title' => 'The Legend of Zelda',
                'releaseDate' => '1986-02-21',
                'description' => 'Un jeu d\'aventure emblématique.',
            ],
            [
                'title' => "Assassin's Creed",
                'releaseDate' => '2007-11-13',
                'description' => 'Action et aventure dans l\'histoire.',
            ],
            [
                'title' => 'FIFA 25',
                'releaseDate' => '2025-09-29',
                'description' => 'Simulation sportive de football.',
            ],
            [
                'title' => 'Final Fantasy XVI',
                'releaseDate' => '2023-06-22',
                'description' => 'RPG fantastique japonais.',
            ],
            [
                'title' => 'Skyrim',
                'releaseDate' => '2011-11-11',
                'description' => 'RPG open world épique.',
            ],
        ];

        $editorCount = 5; // 0 à 4
        $categoryCount = 6; // 0 à 5

        foreach ($videoGames as $data) {
            $videoGame = new VideoGame();
            $videoGame->setTitle($data['title']);
            $videoGame->setReleaseDate(new \DateTime($data['releaseDate']));
            $videoGame->setDescription($data['description']);

            // Choisir un éditeur au hasard
            $editorRef = 'editor_' . rand(0, $editorCount - 1);
            $videoGame->setEditor($this->getReference($editorRef, Editor::class));

            // Choisir 1 à 2 catégories au hasard
            $categoryIndexes = array_rand(range(0, $categoryCount - 1), rand(1,2));
            if (!is_array($categoryIndexes)) {
                $categoryIndexes = [$categoryIndexes];
            }
            foreach ($categoryIndexes as $catIdx) {
                $catRef = 'categorie_' . $catIdx;
                $videoGame->addCategory($this->getReference($catRef, Category::class));
            }

            $manager->persist($videoGame);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            EditorFixtures::class,
        ];
    }
}
