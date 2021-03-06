<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:06 PM
 */

namespace App\DataFixtures;


use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use joshtronic\LoremIpsum;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    private $lipsum;

    public function __construct(LoremIpsum $lipsum)
    {
        $this->lipsum = $lipsum;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 15; $i++) {
            /** @var Book $book */
            $book = $this->getReference('book' . mt_rand(0, 99));
            /** @var User $user */
            $user = $this->getReference('user' . mt_rand(0, 3));
            $content = $this->lipsum->words(mt_rand(5, 20));

            $comment = new Comment($user, $book, $content);

            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class, BookFixtures::class];
    }
}
