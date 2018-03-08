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
    private $lipsum;

    public function __construct(LoremIpsum $lipsum)
    {
        $this->lipsum = $lipsum;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Doctrine\Common\DataFixtures\BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $lipsum = new LoremIpsum();

        for ($i = 0; $i < 20; $i++) {
            $firstName = $lipsum->word();
            $lastName = $lipsum->word();
            $country = $lipsum->word();

            $author = new Author($firstName, $lastName, $country);

            $this->addReference('author' . $i, $author);

            $manager->persist($author);
        }

        $manager->flush();
    }
}
