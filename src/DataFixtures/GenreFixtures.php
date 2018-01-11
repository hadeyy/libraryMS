<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:06 PM
 */

namespace App\DataFixtures;


use App\Entity\Genre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class GenreFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $genres = [
            "science fiction",
            "satire",
            "drama",
            "action and adventure",
            "romance",
            "mystery",
            "horror",
            "self help",
            "health",
            "guide",
            "travel",
            "children's",
            "religion, spirituality & new age",
            "science",
            "history",
            "math",
            "anthology",
            "poetry",
            "encyclopedia",
            "dictionary",
            "comic",
            "art",
            "cooking",
            "diary",
            "journal",
            "prayer",
            "series",
            "trilogy",
            "biography",
            "autobiography",
            "fantasy"
        ];

        for ($i = 0; $i < count($genres); $i++) {
            $genre = new Genre();

            $genre->setName($genres[$i]);

            $this->addReference('genre' . $i, $genre);

            $manager->persist($genre);
        }

        $manager->flush();
    }
}
