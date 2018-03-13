<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/26/2018
 * Time: 11:21 AM
 */

namespace App\Tests\Service;


use App\Entity\Activity;
use App\Entity\Book;
use App\Entity\BookReservation;
use App\Entity\User;
use App\Repository\UserRepository;
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
        $data = [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'username' => 'username',
            'email' => 'email',
            'photo' => 'photo',
            'plainPassword' => 'plainPassword',
        ];

        $userManager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['createUserFromArray'])
            ->getMock();

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

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept(['createArrayFromUser'])
            ->getMock();

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

    public function testRegisterUpdatesUserData()
    {
        $userManager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept([
                'register',
            ])
            ->getMock();

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
    public function testGetFavoriteBooksCallsUserRepository()
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

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, null])
            ->setMethodsExcept(['getFavoriteBooks'])
            ->getMock();

        $favorites = $userManager->getFavoriteBooks($this->user);

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
     * @dataProvider reservationStatusProvider
     *
     * @param string $status
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testGetReservationsByStatusCallsUserRepository(string $status)
    {
        $book = $this->createMock(Book::class);
        $date = $this->createMock(\DateTime::class);
        $reservation = new BookReservation($book, $this->user, $date, $date);
        $reservation->setStatus($status);

        $this->user->addBookReservation($reservation);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepository->expects($this->once())
            ->method('findUserJoinedToReservations')
            ->with($this->user, $status)
            ->will($this->returnArgument(0));

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($userRepository);

        $fileManager = $this->createMock(FileManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, null])
            ->setMethodsExcept(['getReservationsByStatus'])
            ->getMock();

        $reservations = $userManager->findReservationsByStatus($this->user, $status);

        $this->assertTrue(
            $reservations instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertContains(
            $reservation, $reservations,
            "Successfully retrieved user's reservations by status."
        );
    }

    public function reservationStatusProvider()
    {
        return [
            ['reading'],
            ['returned']
        ];
    }

    public function testUpdateProfileChangesUserDataWithNewPhoto()
    {
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

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('deleteFile')
            ->with($this->isType('string'));
        $fileManager->expects($this->once())
            ->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept([
                'updateProfile'
            ])
            ->getMock();

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

    public function testUpdateProfileChangesUserDataWithoutNewPhoto()
    {
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

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))->method('deleteFile');
        $fileManager->expects($this->exactly(0))->method('upload');

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept([
                'updateProfile'
            ])
            ->getMock();

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

    public function testGetActivity()
    {
        $activity = $this->createMock(Activity::class);

        $this->user->addActivity($activity);
        $this->user->addActivity($activity);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getActivity'])
            ->getMock();

        $activities = $userManager->getActivity($this->user);
        $this->assertTrue(
            $activities instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertCount(
            2, $activities,
            'Retrieved activity.html.twig count is correct.'
        );
    }

    public function testFindUsersByRoleCallsUserRepository()
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

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, null])
            ->setMethodsExcept(['findUsersByRole'])
            ->getMock();

        $actual = $userManager->findUsersByRole('some_string');

        $this->assertEquals($expected, $actual, 'Retrieved result matches expected.');
    }

    public function testSavingMethodsCallEntityManager()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->exactly(2))
            ->method('flush');
        $entityManager->expects($this->exactly(2))
            ->method('clear');

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, null])
            ->setMethodsExcept(['save', 'saveChanges'])
            ->getMock();

        $userManager->save($this->user);
        $userManager->saveChanges();
    }

    public function testToggleFavoriteBook()
    {
        $userManager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['addFavorite', 'getFavorites', 'removeFavorite'])
            ->getMock();

        $book = $this->createMock(Book::class);

        $this->assertEmpty(
            $this->user->getFavorites(),
            'User favorites does not contain anything before test.'
        );
        $userManager->addFavorite($this->user, $book);
        $favorites = $userManager->getFavorites($this->user);
        $this->assertContains($book, $favorites, 'Favorites contain expected book.');
        $this->assertEquals(
            1, count($favorites),
            'Favorites contain exactly one book.'
        );

        $userManager->removeFavorite($this->user, $book);
        $this->assertNotContains(
            $book, $this->user->getFavorites(),
            'Book successfully removed from favorites'
        );
        $this->assertEmpty(
            $this->user->getFavorites(),
            'User favorites does not contain anything after test.'
        );
    }

    public function testGetPhotoDirectory()
    {
        $expected = 'some/directory';

        $doctrine = $this->createMock(ManagerRegistry::class);
        $fileManager = $this->createMock(FileManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'some/directory'])
            ->setMethodsExcept(['getPhotoDirectory'])
            ->getMock();

        $actual = $userManager->getPhotoDirectory();

        $this->assertEquals($expected, $actual);
    }
}
