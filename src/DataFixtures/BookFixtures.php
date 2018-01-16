<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:05 PM
 */

namespace App\DataFixtures;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use joshtronic\LoremIpsum;

class BookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $lipsum = new LoremIpsum();
        $languages = ['english', 'french', 'russian', 'spanish', 'arabic', 'latvian'];

        for ($i = 0; $i < 100; $i++) {
            $book = new Book();

            $book->setISBN('978-1-' . mt_rand(10000, 99999) . '-' . mt_rand(100, 999) . '-' . mt_rand(0, 9));
            $book->setTitle($lipsum->words(mt_rand(1, 4)));
            /** @var Author $author */
            $author = $this->getReference('author' . mt_rand(0, 19));
            $book->setAuthor($author);
            $book->setPages(mt_rand(20, 1024));
            $book->setLanguage($languages[mt_rand(0, count($languages) - 1)]);
            /** @var Genre $genre */
            $genre1 = $this->getReference('genre' . mt_rand(0, 9));
            $book->addGenre($genre1);
            /** @var Genre $genre */
            $genre2 = $this->getReference('genre' . mt_rand(10, 19));
            $book->addGenre($genre2);
            /** @var Genre $genre */
            $genre3 = $this->getReference('genre' . mt_rand(20, 29));
            $book->addGenre($genre3);
            $book->setPublisher($lipsum->words(mt_rand(1, 3)));
            $randomDate = mt_rand(1, 28) . '-' . mt_rand(1, 12) . '-' . mt_rand(1800, 2017);
            $book->setPublicationDate(\DateTime::createFromFormat('d-m-Y', $randomDate));
            $book->setAvailableCopies(mt_rand(1, 5));
            $book->setReservedCopies(mt_rand(0, $book->getAvailableCopies()));
            $book->setCover('sample.jpg');
            $book->setAnnotation($lipsum->sentences(7));

            $author->addBook($book);
            $genre1->addBook($book);
            $genre2->addBook($book);
            $genre3->addBook($book);

            $this->addReference('book' . $i, $book);

            $manager->persist($book);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [AuthorFixtures::class, GenreFixtures::class];
    }
}
