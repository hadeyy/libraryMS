<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:06 PM
 */

namespace App\DataFixtures;


use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use joshtronic\LoremIpsum;

class NotificationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $lipsum = new LoremIpsum();

        for ($i = 0; $i < 25; $i++) {
            $notification = new Notification();

            $notification->setTitle($lipsum->words(mt_rand(3, 6)));
            $notification->setContent($lipsum->sentences(2));
            /** @var User $user */
            $user = $this->getReference('user' . mt_rand(0, 3));
            $notification->setReceiver($user);
            mt_rand(0, 1) ?: $notification->setIsSeen(true);

            $manager->persist($notification);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixtures::class];
    }
}
