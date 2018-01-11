<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:04 PM
 */

namespace App\DataFixtures;


use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use joshtronic\LoremIpsum;

class AuthorFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $lipsum = new LoremIpsum();

        for ($i = 0; $i < 20; $i++) {
            $author = new Author();

            $author->setFirstName($lipsum->word());
            $author->setLastName($lipsum->word());
            $author->setCountry($lipsum->word());

            $this->addReference('author' . $i, $author);

            $manager->persist($author);
        }

        $manager->flush();
    }
}
