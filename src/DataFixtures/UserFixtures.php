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
    
    public function load(ObjectManager $manager)
    {
        $user = new User();
        
        $user->setFirstName('John');
        $user->setLastName('Wine');
        $user->setUsername('admin');
        $user->setEmail('JohnHWine@jourrapide.com');
        $user->setPhoto('silhouette.png');
        $password = $this->encoder->encodePassword($user, 'kitten');
        $user->setPassword($password);
        $user->addRole('ROLE_ADMIN');

        $manager->persist($user);
        $manager->flush();
    }
}
