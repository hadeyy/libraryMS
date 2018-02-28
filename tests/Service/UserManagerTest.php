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
use App\Service\PasswordManager;
use App\Service\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserManagerTest extends WebTestCase
{
    public function testRegisterUpdatesUserData()
    {
        $userManager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['register', 'setUserPhoto', 'setUserPassword', 'addRole'])
            ->getMock();

        $user = new User();

        $this->assertEquals('', $user->getPhoto());
        $this->assertEquals('', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        $userManager->register($user, 'photo.jpg', 'pass123', 'role1');

        $this->assertEquals('photo.jpg', $user->getPhoto(), 'User photo has been updated.');
        $this->assertEquals('pass123', $user->getPassword(), 'User password has been updated.');
        $this->assertContains('role1', $user->getRoles(), 'User roles have been updated.');
    }

    public function testGetFavoriteBooksCallsUserRepository()
    {
        $book = new Book();
        $user = new User();
        $user->addFavorite($book);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepository->expects($this->once())
            ->method('getFavoriteBooks')
            ->willReturn(new ArrayCollection([$book]));

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($userRepository);

        $fileManager = $this->createMock(FileManager::class);
        $passwordManager = $this->createMock(PasswordManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $passwordManager, null])
            ->setMethodsExcept(['getFavoriteBooks'])
            ->getMock();

        $favorites = $userManager->getFavoriteBooks($user);

        $this->assertTrue(
            $favorites instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertContains($book, $favorites, "Successfully retrieved user's favorite books.");
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
        $reservation = new BookReservation();
        $reservation->setStatus($status);

        $user = new User();
        $user->addBookReservation($reservation);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepository->expects($this->once())
            ->method('findUserJoinedToReservations')
            ->with($user, $status)
            ->will($this->returnArgument(0));

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($userRepository);

        $fileManager = $this->createMock(FileManager::class);
        $passwordManager = $this->createMock(PasswordManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $passwordManager, null])
            ->setMethodsExcept(['getReservationsByStatus'])
            ->getMock();

        $reservations = $userManager->getReservationsByStatus($user, $status);

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

    public function testChangePhotoFromPathToFileCreatesAFile()
    {
        $user = new User();
        $user->setPhoto('test.jpg');

        $filePath = 'test_file.jpg';
        fopen($filePath, 'w');

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('createFileFromPath')
            ->willReturn(new File($filePath));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $passwordManager = $this->createMock(PasswordManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $passwordManager, ''])
            ->setMethodsExcept([
                'changePhotoFromPathToFile',
                'setUserPhoto',
                'setPhotoPath',
                'setPhotoName',
                'getPhotoPath'
            ])
            ->getMock();

        $this->assertTrue(is_string($user->getPhoto()), "User's photo is stored as string.");
        $userManager->changePhotoFromPathToFile($user);
        $this->assertTrue(
            $user->getPhoto() instanceof File,
            "User's photo successfully changed from string to an instance of File."
        );

        unlink($filePath);
    }

    /**
     * @dataProvider photoProvider
     * @param $photo
     * @param bool $isString
     */
    public function testUpdateProfileChangesPhotoAndPassword($photo, bool $isString)
    {
        $user = new User();
        $user->setPhoto($photo);
        $user->setPassword('1234');
        $originalPhoto = $user->getPhoto();
        $originalPassword = $user->getPassword();

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->any())
            ->method('deleteFile')
            ->with($this->isType('string'));
        $fileManager->expects($this->any())
            ->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $passwordManager = $this->createMock(PasswordManager::class);
        $passwordManager->expects($this->once())
            ->method('encode')
            ->willReturn('password');

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $passwordManager, 'path/to/directory'])
            ->setMethodsExcept([
                'updateProfile',
                'setPhotoPath',
                'getPhotoPath',
                'setPhotoName',
                'getPhotoName',
                'setUserPhoto',
                'setUserPassword',
                'getUserPhoto',
                'getUserPassword'
            ])
            ->getMock();
        $userManager->setPhotoPath('path/to/photo');
        $userManager->setPhotoName('filename');

        $userManager->updateProfile($user);

        $this->assertEquals('filename', $user->getPhoto(), "User's photo has been updated.");
        $this->assertEquals('password', $user->getPassword(), "User's password has benn updated.");
        $this->assertNotEquals(
            $originalPhoto, $user->getPhoto(),
            'Updated photo is not equal to original.');
        $this->assertNotEquals(
            $originalPassword, $user->getPassword(),
            'Updated password is not equal to original.'
        );

        $isString ?: unlink('test_photo.jpg');
    }

    public function photoProvider()
    {
        $filePath = 'test_photo.jpg';
        fopen($filePath, 'w');

        return [
            [new UploadedFile($filePath, $filePath), true],
            [$filePath, false]
        ];
    }

    public function testGetActivity()
    {
        $user = new User();
        $user->addActivity(new Activity());
        $user->addActivity(new Activity());

        $userManager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getActivity'])
            ->getMock();

        $activities = $userManager->getActivity($user);
        $this->assertTrue(
            $activities instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertCount(2, $activities, 'Retrieved activity count is correct.');
    }

    public function testFindUsersByRoleCallsUserRepository()
    {
        $expected = new ArrayCollection([new User()]);

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
        $passwordManager = $this->createMock(PasswordManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $passwordManager, null])
            ->setMethodsExcept(['findUsersByRole'])
            ->getMock();

        $actual = $userManager->findUsersByRole('some_string');

        $this->assertEquals($expected, $actual, 'Retrieved result matches expected.');
    }

    public function testSavingMethodsCallEntityManagerMethods()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->exactly(2))
            ->method('flush');

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $passwordManager = $this->createMock(PasswordManager::class);

        $userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, $passwordManager, null])
            ->setMethodsExcept(['save', 'saveChanges'])
            ->getMock();

        $userManager->save(new User());
        $userManager->saveChanges();
    }

    public function testGetUserPhoto()
    {
        $user = new User();
        $user->setPhoto('photo.jpg');

        $userManager = $this->getMockBuilder(UserManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getUserPhoto'])
            ->getMock();

        $actual = $userManager->getUserPhoto($user);

        $this->assertEquals('photo.jpg', $actual, 'Retrieved result matches expected.');
    }
}
