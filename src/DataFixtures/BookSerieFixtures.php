<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:05 PM
 */

namespace App\DataFixtures;


use App\Entity\Book;
use App\Entity\BookSerie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BookSerieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 4; $i++) {
            $bookSerie = new BookSerie();

            /** @var Book $book */
            $book = $this->getReference('book' . mt_rand(0, 99));
            $bookSerie->addBook($book);

            $book->setSerie($bookSerie);

            $manager->persist($bookSerie);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [BookFixtures::class];
    }
}
