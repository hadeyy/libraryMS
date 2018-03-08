<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 12:15 PM
 */

namespace App\DataFixtures;


use App\Entity\User;
use App\Service\PasswordManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    private $passwordManager;

    public function __construct(PasswordManager $passwordManager)
    {
        $this->passwordManager = $passwordManager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Doctrine\Common\DataFixtures\BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $photo = 'silhouette.png';
        $plainPassword = 'pass123';

        $admin = new User(
            'John',
            'Wine',
            'admin',
            'JohnHWine@jourrapide.com',
            'silhouette.png',
            $plainPassword,
            ['ROLE_USER', 'ROLE_ADMIN']
        );
        $admin->setPassword($this->passwordManager->encode($admin));
        $manager->persist($admin);
        $this->addReference('user0', $admin);

        $librarian = new User(
            'Melinda',
            'Stephens',
            'librarian',
            'MelindaRStephens@teleworm.us',
            $photo,
            $plainPassword,
            ['ROLE_USER', 'ROLE_LIBRARIAN']
        );
        $librarian->setPassword($this->passwordManager->encode($librarian));
        $manager->persist($librarian);
        $this->addReference('user1', $librarian);

        $reader = new User(
            'Wayne',
            'Gutierrez',
            'reader',
            'WayneTGutierrez@rhyta.com',
            $photo,
            $plainPassword,
            ['ROLE_USER', 'ROLE_READER']
        );
        $reader->setPassword($this->passwordManager->encode($reader));
        $manager->persist($reader);
        $this->addReference('user2', $reader);

        $user = new User(
            'Athena',
            'Carswell',
            'user',
            'AthenaMCarswell@rhyta.com',
            $photo,
            $plainPassword,
            ['ROLE_USER']
        );
        $user->setPassword($this->passwordManager->encode($user));
        $manager->persist($user);
        $this->addReference('user3', $user);

        $manager->flush();
    }
}
