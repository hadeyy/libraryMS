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
        $languages = ['english', 'french', 'russian', 'spanish', 'arabic', 'latvian'];

        for ($i = 0; $i < 100; $i++) {
            $ISBN = mt_rand(1000, 9999) . '-' . mt_rand(10, 99) . '-' . mt_rand(100, 999) . '-' . mt_rand(0, 9);
            $title = $this->lipsum->words(mt_rand(1, 4));
            /** @var Author $author */
            $author = $this->getReference('author' . mt_rand(0, 19));
            $pages = mt_rand(20, 1024);
            $language = $languages[mt_rand(0, count($languages) - 1)];
            /** @var Genre $genre1 */
            $genre1 = $this->getReference('genre' . mt_rand(0, 9));
            /** @var Genre $genre2 */
            $genre2 = $this->getReference('genre' . mt_rand(10, 19));
            /** @var Genre $genre3 */
            $genre3 = $this->getReference('genre' . mt_rand(20, 29));
            $publisher = $this->lipsum->words(mt_rand(1, 3));
            $randomDate = mt_rand(1, 28) . '-' . mt_rand(1, 12) . '-' . mt_rand(1800, 2017);
            $publicationDate = \DateTime::createFromFormat('d-m-Y', $randomDate);
            $availableCopies = mt_rand(1, 5);
            $cover = 'sample.jpg';
            $annotation = $this->lipsum->sentences(7);

            $book = new Book(
                $ISBN,
                $title,
                $author,
                $pages,
                $language,
                $publisher,
                $publicationDate,
                $availableCopies,
                $cover,
                $annotation
            );

            $book->addGenre($genre1);
            $book->addGenre($genre2);
            $book->addGenre($genre3);

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
