<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['name' => 'RPG'],
            ['name' => 'Aventure'],
            ['name' => 'Action'],
            ['name' => 'Stratégie'],
            ['name' => 'Simulation'],
            ['name' => 'Sport'],
            ['name' => 'Roguelike'],
            ['name' => 'Survie'],
            ['name' => 'Battle royale'],
        ];
        foreach ($categories as $key => $data) {
            $categorie = new Category();
            $categorie->setName($data['name']);
            $manager->persist($categorie);
            $this->addReference('categorie_' . $key, $categorie);
        }
        $manager->flush();
    }
}
