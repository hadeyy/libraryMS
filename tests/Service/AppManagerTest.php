<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/2/2018
 * Time: 1:25 PM
 */

namespace App\Tests\Service;


use App\Entity\Comment;
use App\Entity\User;
use App\Service\AppManager;
use App\Service\FileManager;
use App\Service\UserManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppManagerTest extends WebTestCase
{
    private $user;

    public function setUp()
    {
        $this->user = new User(
            'firstName',
            'lastName',
            'username',
            'email@email.em',
            'photo.png',
            'plainPassword'
        );
    }

    /**
     * @param string $role
     * @dataProvider userRoleProvider
     */
    public function testChangeRole(string $role)
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $userManager = $this->createMock(UserManager::class);

        $appManager = new AppManager($doctrine, $fileManager, $userManager);

        $appManager->changeRole($this->user, $role);
        $this->assertEquals(['ROLE_USER', $role], $this->user->getRoles());
    }

    public function userRoleProvider()
    {
        return [
            ['ROLE_READER'],
            ['ROLE_LIBRARIAN'],
            ['ROLE_ADMIN']
        ];
    }

    public function testDeleteUser()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('deleteFile')
            ->with($this->isType('string'));
        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('getPhotoDirectory')
            ->willReturn('path/to/directory');

        $appManager = new AppManager($doctrine, $fileManager, $userManager);

        $appManager->deleteUser($this->user);
    }

    public function testDeleteComment()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Comment::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $userManager = $this->createMock(UserManager::class);

        $appManager = new AppManager($doctrine, $fileManager, $userManager);

        $comment = $this->createMock(Comment::class);
        $appManager->deleteComment($comment);
    }
}
