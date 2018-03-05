<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/2/2018
 * Time: 1:25 PM
 */

namespace App\Tests\Service;


use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Service\AppManager;
use App\Service\FileManager;
use App\Service\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppManagerTest extends WebTestCase
{
    public function testCreateUser()
    {
        $appManager = $this->getMockBuilder(AppManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['createUser'])
            ->getMock();

        $user = $appManager->createUser();
        $this->assertTrue(
            $user instanceof User,
            'Result is an instance of User class.'
        );
    }

    public function testGetAllActivityCallsActivityRepository()
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $activityRepository->expects($this->once())
            ->method('findRecentActivity')
            ->willReturn(new ArrayCollection());

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($activityRepository);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $userManager = $this->createMock(UserManager::class);

        $appManager = $this->getMockBuilder(AppManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $userManager])
            ->setMethodsExcept(['getAllActivity'])
            ->getMock();

        $result = $appManager->getAllActivity();
        $this->assertEquals(new ArrayCollection(), $result);
    }

    public function testRemoveAndSavingMethodsCallEntityManagerMethods()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->exactly(3))
            ->method('flush');
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isType('object'));

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $userManager = $this->createMock(UserManager::class);

        $appManager = $this->getMockBuilder(AppManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $userManager])
            ->setMethodsExcept(['save', 'saveChanges', 'remove'])
            ->getMock();

        $user = new User();
        $book = new Book();

        $appManager->save($user);
        $appManager->saveChanges();
        $appManager->remove(new Comment($user, $book));
    }

    public function testChangeRoleResetsRoles()
    {
        $appManager = $this->getMockBuilder(AppManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['changeRole'])
            ->getMock();

        $user = new User();
        $user->addRole('ROLE_READER');
        $user->addRole('ROLE_LIBRARIAN');
        $user->addRole('ROLE_ADMIN');
        $user->addRole('test_role');

        $this->assertEquals(5, count($user->getRoles()));
        $appManager->changeRole($user, 'ROLE_UPDATED');
        $this->assertEquals(2, count($user->getRoles()));
        $this->assertContains('ROLE_UPDATED', $user->getRoles());
    }

    public function testDeleteUserCallsFileAndEntityManagerMethods()
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
        $userManager->expects($this->once())
            ->method('getPhoto')
            ->with($this->isInstanceOf(User::class))
            ->willReturn('filename');

        $appManager = $this->getMockBuilder(AppManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $userManager])
            ->setMethodsExcept(['deleteUser', 'remove', 'saveChanges'])
            ->getMock();

        $appManager->deleteUser(new User());
    }

    public function testDeleteCommentCallsEntityManager()
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

        $appManager = $this->getMockBuilder(AppManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $userManager])
            ->setMethodsExcept(['deleteComment', 'remove', 'saveChanges'])
            ->getMock();

        $appManager->deleteComment(new Comment(new User(), new Book()));
    }
}
