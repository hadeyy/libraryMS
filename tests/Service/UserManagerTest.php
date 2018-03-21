<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/26/2018
 * Time: 11:21 AM
 */

namespace App\Tests\Service;


use App\Entity\Book;
use App\Entity\BookReservation;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ActivityManager;
use App\Service\FileManager;
use App\Service\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserManagerTest extends WebTestCase
{
    public $user;

    public function setUp()
    {
        $this->user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );
    }

    public function testCreateUserFromArray()
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $fileManager = $this->createMock(FileManager::class);
        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $data = [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'username' => 'username',
            'email' => 'email',
            'photo' => 'photo',
            'plainPassword' => 'plainPassword',
        ];
        $user = $userManager->createUserFromArray($data);

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

        return $user;
    }

    /**
     * @depends testCreateUserFromArray
     * @param User $user
     */
    public function testCreateArrayFromUser(User $user)
    {
        $file = $this->createMock(File::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('createFileFromPath')
            ->with($this->isType('string'))
            ->willReturn($file);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $data = $userManager->createArrayFromUser($user);

        $this->assertTrue(is_array($data), 'Result is an array.');
        $this->assertCount(
            5, $data,
            'Array contains the correct number of elements.'
        );
        $this->assertArrayHasKey('firstName', $data);
        $this->assertEquals($data['firstName'], $user->getFirstName());
        $this->assertArrayHasKey('lastName', $data);
        $this->assertEquals($data['lastName'], $user->getLastName());
        $this->assertArrayHasKey('username', $data);
        $this->assertEquals($data['username'], $user->getUsername());
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals($data['email'], $user->getEmail());
        $this->assertArrayHasKey('photo', $data);
        $this->assertEquals($data['photo'], $file);
    }

    public function testRegister()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $this->assertEquals('photo', $this->user->getPhoto());
        $this->assertEquals('123456', $this->user->getPassword());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());

        $userManager->register($this->user, 'photo.jpg', 'pass123', 'role1');

        $this->assertEquals(
            'photo.jpg', $this->user->getPhoto(),
            'User photo has been updated.'
        );
        $this->assertEquals(
            'pass123', $this->user->getPassword(),
            'User password has been updated.'
        );
        $this->assertEquals(
            ['ROLE_USER', 'role1'], $this->user->getRoles(),
            'User roles have been updated.'
        );
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testFindFavoriteBooks()
    {
        $book = $this->createMock(Book::class);
        $this->user->addFavorite($book);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepository->expects($this->once())
            ->method('findUserJoinedToFavoriteBooks')
            ->with($this->user)
            ->will($this->returnArgument(0));

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($userRepository);

        $fileManager = $this->createMock(FileManager::class);
        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $favorites = $userManager->findFavoriteBooks($this->user);

        $this->assertTrue(
            $favorites instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertContains(
            $book, $favorites,
            "Successfully retrieved user's favorite books."
        );
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testFindReservationsByStatus()
    {
        $book = $this->createMock(Book::class);
        $date = $this->createMock(\DateTime::class);
        $reservation = new BookReservation($book, $this->user, $date, $date);
        $reservation->setStatus('status');

        $this->user->addBookReservation($reservation);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepository->expects($this->once())
            ->method('findUserJoinedToReservationsByStatus')
            ->with($this->user, $this->isType('string'))
            ->will($this->returnArgument(0));

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($userRepository);

        $fileManager = $this->createMock(FileManager::class);
        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $reservations = $userManager->findReservationsByStatus($this->user, 'status');

        $this->assertTrue(
            $reservations instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertContains(
            $reservation, $reservations,
            "Successfully retrieved user's reservations by status."
        );
    }

    public function testUpdateProfileWithNewPhoto()
    {
        $entityManager = $this->createMock(EntityManager::class);
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
        $fileManager->expects($this->once())
            ->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );

        $filePath = 'test_update_photo.jpg';
        fopen($filePath, 'w');
        $newData = [
            'firstName' => 'Test',
            'lastName' => 'Testerson',
            'username' => 'tester',
            'email' => 'test@test.er',
            'photo' => new UploadedFile($filePath, $filePath),
        ];
        $userManager->updateProfile($user, $newData);

        $this->assertNotEquals(
            'firstName', $user->getFirstName(),
            "Updated first name is not equal to original."
        );
        $this->assertNotEquals(
            'lastName', $user->getLastName(),
            "Updated last name is not equal to original."
        );
        $this->assertNotEquals(
            'username', $user->getUsername(),
            "Updated username is not equal to original."
        );
        $this->assertNotEquals(
            'email', $user->getEmail(),
            "Updated email is not equal to original."
        );
        $this->assertNotEquals(
            'photo', $user->getPhoto(),
            'Updated photo is not equal to original.'
        );

        unlink('test_update_photo.jpg');
    }

    public function testUpdateProfileWithoutNewPhoto()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))->method('deleteFile');
        $fileManager->expects($this->exactly(0))->method('upload');

        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $user = new User(
            'firstName',
            'lastName',
            'username',
            'email',
            'photo',
            'plainPassword'
        );

        $newData = [
            'firstName' => 'Test',
            'lastName' => 'Testerson',
            'username' => 'tester',
            'email' => 'test@test.er',
            'photo' => null,
        ];

        $userManager->updateProfile($user, $newData);

        $this->assertNotEquals(
            'firstName', $user->getFirstName(),
            "Updated first name is not equal to original."
        );
        $this->assertNotEquals(
            'lastName', $user->getLastName(),
            "Updated last name is not equal to original."
        );
        $this->assertNotEquals(
            'username', $user->getUsername(),
            "Updated username is not equal to original."
        );
        $this->assertNotEquals(
            'email', $user->getEmail(),
            "Updated email is not equal to original."
        );
        $this->assertEquals(
            'photo', $user->getPhoto(),
            'Original photo was not changed.'
        );
    }

    public function testFindUsersByRole()
    {
        $expected = new ArrayCollection([$this->user]);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepository->expects($this->once())
            ->method('findUsersByRole')
            ->with('some_string')
            ->willReturn($expected);

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($userRepository);

        $fileManager = $this->createMock(FileManager::class);
        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $actual = $userManager->findUsersByRole('some_string');

        $this->assertEquals($expected, $actual, 'Retrieved result matches expected.');
    }

    public function testGetPhotoDirectory()
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $fileManager = $this->createMock(FileManager::class);
        $activityManager = $this->createMock(ActivityManager::class);

        $userManager = new UserManager($doctrine, $fileManager, $activityManager, 'path/to/directory');

        $actual = $userManager->getPhotoDirectory();

        $this->assertEquals('path/to/directory', $actual);
    }
}
