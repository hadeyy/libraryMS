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
    public function testEncodeCallsUserPasswordEncoderInterface()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getPlainPassword')
            ->willReturn('plainPassword');

        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($this->isInstanceOf(User::class), $this->isType('string'))
            ->willReturn('encoded password');
        $doctrine = $this->createMock(ManagerRegistry::class);

        $passwordManager = $this->getMockBuilder(PasswordManager::class)
            ->setConstructorArgs([$passwordEncoder, $doctrine])
            ->setMethodsExcept(['encode'])
            ->getMock();

        $result = $passwordManager->encode($user);

        $this->assertEquals(
            'encoded password', $result,
            'Retrieved result matches expected.'
        );
    }

    public function testChangePasswordUpdatesUserData()
    {
        $user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );
        $newPassword = 'newPass';

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $passwordManager = $this->getMockBuilder(PasswordManager::class)
            ->setConstructorArgs([$passwordEncoder, $doctrine])
            ->setMethodsExcept(['changePassword', 'saveChanges'])
            ->getMock();
        $passwordManager->expects($this->once())
            ->method('encode')
            ->with($this->isInstanceOf(User::class))
            ->willReturn('newPass');

        $passwordManager->changePassword($user, $newPassword);
        $this->assertEquals($newPassword, $user->getPassword());
    }
}
