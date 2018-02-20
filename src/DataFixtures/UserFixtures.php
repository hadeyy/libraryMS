<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 12:15 PM
 */

namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Doctrine\Common\DataFixtures\BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Wine');
        $admin->setUsername('admin');
        $admin->setEmail('JohnHWine@jourrapide.com');
        $admin->setPhoto('silhouette.png');
        $password = $this->encoder->encodePassword($admin, 'kitten');
        $admin->setPassword($password);
        $admin->addRole('ROLE_ADMIN');
        $manager->persist($admin);
        $this->addReference('user0', $admin);

        $librarian = new User();
        $librarian->setFirstName('Melinda');
        $librarian->setLastName('Stephens');
        $librarian->setUsername('librarian');
        $librarian->setEmail('MelindaRStephens@teleworm.us');
        $librarian->setPhoto('silhouette.png');
        $password = $this->encoder->encodePassword($librarian, 'kitten');
        $librarian->setPassword($password);
        $librarian->addRole('ROLE_LIBRARIAN');
        $manager->persist($librarian);
        $this->addReference('user1', $librarian);

        $user1 = new User();
        $user1->setFirstName('Wayne');
        $user1->setLastName('Gutierrez');
        $user1->setUsername('reader');
        $user1->setEmail('WayneTGutierrez@rhyta.com');
        $user1->setPhoto('silhouette.png');
        $password = $this->encoder->encodePassword($user1, 'kitten');
        $user1->setPassword($password);
        $user1->addRole('ROLE_READER');
        $manager->persist($user1);
        $this->addReference('user2', $user1);

        $user = new User();
        $user->setFirstName('Athena');
        $user->setLastName('Carswell');
        $user->setUsername('user');
        $user->setEmail('AthenaMCarswell@rhyta.com');
        $user->setPhoto('silhouette.png');
        $password = $this->encoder->encodePassword($user, 'kitten');
        $user->setPassword($password);
        $user->addRole('ROLE_USER');
        $manager->persist($user);
        $this->addReference('user3', $user);

        $manager->flush();
    }
}
