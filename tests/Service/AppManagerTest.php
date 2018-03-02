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

        $appManager = $this->getMockBuilder(AppManager::class)
            ->setConstructorArgs([$doctrine, $fileManager])
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

        $appManager = $this->getMockBuilder(AppManager::class)
            ->setConstructorArgs([$doctrine, $fileManager])
            ->setMethodsExcept(['save', 'saveChanges', 'remove'])
            ->getMock();

        $user = new User();
        $book = new Book();

        $appManager->save($user);
        $appManager->saveChanges();
        $appManager->remove(new Comment($user, $book));
    }
}
