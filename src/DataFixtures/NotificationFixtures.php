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
    private $lipsum;

    public function __construct(LoremIpsum $lipsum)
    {
        $this->lipsum = $lipsum;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 25; $i++) {

            $title = $this->lipsum->words(mt_rand(3, 6));
            $content = $this->lipsum->sentences(2);
            /** @var User $receiver */
            $receiver = $this->getReference('user' . mt_rand(0, 3));

            $notification = new Notification($title, $content, $receiver);
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
