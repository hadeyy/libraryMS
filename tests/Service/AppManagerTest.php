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
    public function testCreateUserAddsDataToUser()
    {
        $appManager = $this->getMockBuilder(AppManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['createUser'])
            ->getMock();

        $data = [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'username' => 'username',
            'email' => 'email',
            'photo' => 'photo',
            'plainPassword' => 'plainPassword',
        ];

        $user = $appManager->createUser($data);
        $this->assertTrue(
            $user instanceof User,
            'Result is an instance of User class.'
        );
        $this->assertEquals('firstName', $user->getFirstName());
        $this->assertEquals('lastName', $user->getLastName());
        $this->assertEquals('username', $user->getUsername());
        $this->assertEquals('email', $user->getEmail());
        $this->assertEquals('photo', $user->getPhoto());
        $this->assertEquals('plainPassword', $user->getPlainPassword());
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
            ->setMethodsExcept(['findAllActivity'])
            ->getMock();

        $result = $appManager->findAllActivity();
        $this->assertEquals(new ArrayCollection(), $result);
    }

    public function testRemoveAndSavingMethodsCallEntityManager()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->exactly(3))
            ->method('flush');
        $entityManager->expects($this->exactly(3))
            ->method('clear');
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

        $user = $this->createMock(User::class);
        $book = $this->createMock(Book::class);

        $appManager->save($user);
        $appManager->saveChanges();
        $appManager->remove(new Comment($user, $book, 'comment'));
    }

    public function testChangeRoleResetsRoles()
    {
        $appManager = $this->getMockBuilder(AppManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['changeRole'])
            ->getMock();

        $user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );
        $user->addRole('ROLE_READER');
        $user->addRole('ROLE_LIBRARIAN');
        $user->addRole('ROLE_ADMIN');
        $user->addRole('test_role');

        $expected = ['ROLE_USER', 'ROLE_UPDATED'];

        $this->assertEquals(5, count($user->getRoles()));
        $appManager->changeRole($user, 'ROLE_UPDATED');
        $this->assertEquals(2, count($user->getRoles()));
        $this->assertContains('ROLE_UPDATED', $user->getRoles());
        $this->assertEquals($expected, $user->getRoles());
    }

    public function testDeleteUserCallsFileAndEntityManagers()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->once())
            ->method('flush');
        $entityManager->expects($this->once())
            ->method('clear');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->exactly(2))
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('deleteFile')
            ->with($this->isType('string'));

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept(['getPhotoDirectory'])
            ->getMock();

        $appManager = $this->getMockBuilder(AppManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $userManager])
            ->setMethodsExcept(['deleteUser', 'remove', 'saveChanges'])
            ->getMock();

        $user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );
        $appManager->deleteUser($user);
    }

    public function testDeleteCommentCallsEntityManager()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Comment::class));
        $entityManager->expects($this->once())
            ->method('flush');
        $entityManager->expects($this->once())
            ->method('clear');

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

        $user = $this->createMock(User::class);
        $book = $this->createMock(Book::class);

        $appManager->deleteComment(new Comment($user, $book, 'comment'));
    }

    public function testFindActivityByDateLimitCallsActivityRepository()
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $activityRepository->expects($this->once())
            ->method('findActivityByDateLimit');

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
            ->setMethodsExcept(['findActivityByDateLimit'])
            ->getMock();

        $appManager->findActivityByDateLimit('today');
    }
}
