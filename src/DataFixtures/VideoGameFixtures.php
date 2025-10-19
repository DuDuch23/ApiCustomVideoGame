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
            ['title' => 'The Legend of Zelda', 'releaseDate' => '1986-02-21', 'description' => 'Un jeu d\'aventure emblématique.'],
            ["title" => "Assassin's Creed", 'releaseDate' => '2007-11-13', 'description' => 'Action et aventure dans l\'histoire.'],
            ['title' => 'FIFA 25', 'releaseDate' => '2025-09-29', 'description' => 'Simulation sportive de football.'],
            ['title' => 'Final Fantasy XVI', 'releaseDate' => '2023-06-22', 'description' => 'RPG fantastique japonais.'],
            ['title' => 'Skyrim', 'releaseDate' => '2011-11-11', 'description' => 'RPG open world épique.'],
            ['title' => 'Cyberpunk 2077', 'releaseDate' => '2020-12-10', 'description' => 'Futur dystopique en monde ouvert.'],
            ['title' => 'Horizon Forbidden West', 'releaseDate' => '2025-10-25', 'description' => 'Exploration et combat contre des machines géantes.'],
            ['title' => 'God of War: Ragnarok', 'releaseDate' => '2025-11-05', 'description' => 'Action-aventure mythologique nordique.'],
            ['title' => 'Elden Ring', 'releaseDate' => '2022-02-25', 'description' => 'RPG d\'action avec un monde ouvert interconnecté.'],
            ['title' => 'Call of Duty: Modern Warfare VII', 'releaseDate' => '2025-10-30', 'description' => 'FPS intense avec campagne et multijoueur.'],
            ['title' => 'Minecraft 2', 'releaseDate' => '2025-11-15', 'description' => 'Construction, survie et exploration infinie.'],
            ['title' => 'Resident Evil 9', 'releaseDate' => '2025-10-22', 'description' => 'Survival horror et scénario terrifiant.'],
            ['title' => 'The Witcher 4', 'releaseDate' => '2025-11-10', 'description' => 'Suite de la saga RPG médiévale fantastique.'],
            ['title' => 'Forza Horizon 6', 'releaseDate' => '2025-10-28', 'description' => 'Simulation de course avec monde ouvert dynamique.'],
            ['title' => 'Animal Crossing: New Horizons 2', 'releaseDate' => '2023-03-15', 'description' => 'Simulation de vie et de village animé.'],
            ['title' => 'Super Mario Odyssey 2', 'releaseDate' => '2025-11-12', 'description' => 'Plateforme 3D colorée et inventive.'],
            ['title' => 'Metroid Prime 4', 'releaseDate' => '2025-10-27', 'description' => 'Exploration spatiale et combats intenses.'],
            ['title' => 'Gran Turismo 8', 'releaseDate' => '2025-11-03', 'description' => 'Simulation automobile réaliste et compétitive.'],
            ['title' => 'Hollow Knight: Silksong', 'releaseDate' => '2025-11-18', 'description' => 'Metroidvania sombre et immersif.'],
            ['title' => 'Splatoon 3', 'releaseDate' => '2022-09-09', 'description' => 'FPS multijoueur coloré et original.'],
            ['title' => 'Diablo IV', 'releaseDate' => '2023-06-06', 'description' => 'Action RPG sombre et addictif.'],
            ['title' => 'Bayonetta 3', 'releaseDate' => '2025-10-21', 'description' => 'Action frénétique avec une héroïne emblématique.'],
            ['title' => 'Street Fighter VI', 'releaseDate' => '2025-11-07', 'description' => 'Jeu de combat classique avec nouveaux personnages.'],
            ['title' => 'Legend of Mana Remastered', 'releaseDate' => '2025-10-26', 'description' => 'RPG classique remis au goût du jour.'],
            ['title' => 'Pokemon Scarlet & Violet 2', 'releaseDate' => '2025-11-14', 'description' => 'Capture et aventure dans un monde coloré.'],
            ['title' => 'Tekken 8', 'releaseDate' => '2025-11-02', 'description' => 'Combat 3D intense et techniques variées.'],
            ['title' => 'Monster Hunter Rise 2', 'releaseDate' => '2025-10-29', 'description' => 'Chasse épique de monstres gigantesques.'],
            ['title' => 'The Sims 5', 'releaseDate' => '2025-11-11', 'description' => 'Simulation de vie moderne et créative.'],
            ['title' => 'F-Zero GX Remake', 'releaseDate' => '2025-10-24', 'description' => 'Courses futuristes à grande vitesse.'],
            ['title' => 'Dead Space Remake', 'releaseDate' => '2025-11-16', 'description' => 'Survival horror dans l\'espace.'],
            ['title' => 'Ghost of Tsushima 2', 'releaseDate' => '2025-11-19', 'description' => 'Aventure samouraï en monde ouvert.'],
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
