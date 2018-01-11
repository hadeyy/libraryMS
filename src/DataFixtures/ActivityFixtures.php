<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:04 PM
 */

namespace App\DataFixtures;


use App\Entity\Activity;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use joshtronic\LoremIpsum;

class ActivityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $lipsum = new LoremIpsum();

        for ($i = 0; $i < 10; $i++) {
            $activity = new Activity();

            $activity->setTitle($lipsum->words(mt_rand(3, 6)));
            $activity->setContent($lipsum->sentences(2));
            /** @var Book $book */
            $book = $this->getReference('book' . mt_rand(0, 99));
            $activity->setBook($book);
            /** @var User $user */
            $user = $this->getReference('user' . mt_rand(0, 3));
            $activity->setUser($user);

            $book->addActivity($activity);
            $user->addActivity($activity);

            $manager->persist($activity);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [BookFixtures::class, UserFixtures::class];
    }
}
