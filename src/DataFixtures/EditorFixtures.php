<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Editor;

class EditorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $editors = [
            ['name' => 'Nintendo', 'country' => 'Japon'],
            ['name' => 'Ubisoft', 'country' => 'France'],
            ['name' => 'Electronic Arts', 'country' => 'États-Unis'],
            ['name' => 'Square Enix', 'country' => 'Japon'],
            ['name' => 'Bethesda', 'country' => 'États-Unis'],
        ];
        foreach ($editors as $key => $data) {
            $editor = new Editor();
            $editor->setName($data['name']);
            $editor->setCountry($data['country']);
            $manager->persist($editor);
            $this->addReference('editor_' . $key, $editor);
        }
        $manager->flush();
    }
}
