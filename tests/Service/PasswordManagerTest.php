<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/28/2018
 * Time: 3:24 PM
 */

namespace App\Tests\Service;


use App\Entity\User;
use App\Service\PasswordManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordManagerTest extends WebTestCase
{
    public function testEncode()
    {
        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($this->isInstanceOf(User::class), $this->isType('string'));

        $doctrine = $this->createMock(ManagerRegistry::class);

        $passwordManager = new PasswordManager($passwordEncoder, $doctrine);

        $user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );
        $passwordManager->encode($user);
    }

    public function testChangePassword()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($this->isInstanceOf(User::class), $this->isType('string'))
            ->willReturn('encodedPassword');

        $passwordManager = new PasswordManager($passwordEncoder, $doctrine);

        $user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );
        $this->assertEquals(
            '123456', $user->getPassword(),
            'User has the default password.'
        );
        $passwordManager->changePassword($user, 'newPassword');
        $this->assertEquals(
            'encodedPassword', $user->getPassword(),
            'Password has been encoded.'
        );
    }
}
