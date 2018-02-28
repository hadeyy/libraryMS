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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordManagerTest extends WebTestCase
{
    public function testEncodeCallsUserPasswordEncoderInterface()
    {
        $user = new User();
        $user->setPlainPassword('password');

        $passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $passwordEncoder->expects($this->once())
            ->method('encodePassword')
            ->with($this->isInstanceOf(User::class), $this->isType('string'))
            ->willReturn('encoded password');

        $passwordManager = $this->getMockBuilder(PasswordManager::class)
            ->setConstructorArgs([$passwordEncoder])
            ->setMethodsExcept(['encode'])
            ->getMock();

        $result = $passwordManager->encode($user);

        $this->assertEquals(
            'encoded password', $result,
            'Retrieved result matches expected.'
        );
    }
}
