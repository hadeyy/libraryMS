<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/1/2018
 * Time: 10:43 AM
 */

namespace App\Tests\Service;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\BookReservation;
use App\Entity\User;
use App\Repository\BookReservationRepository;
use App\Service\BookReservationManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookReservationManagerTest extends WebTestCase
{
    public function testCreate()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(BookReservation::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $reservationManager = new BookReservationManager($doctrine);

        $book = new Book(
            'ISBN',
            'title',
            $this->createMock(Author::class),
            123,
            'language',
            'publisher',
            new \DateTime(),
            1,
            'cover',
            'annotation'
        );
        $reader = $this->createMock(User::class);
        $dateFrom = new \DateTime();
        $dateTo = new \DateTime();

        $reservationManager->create($book, $reader, $dateFrom, $dateTo);

        $this->assertEquals(
            0, $book->getAvailableCopies(),
            "Book's available copies have been updated."
        );
        $this->assertEquals(
            1, $book->getReservedCopies(),
            "Book's reserved copies have been updated."
        );
    }

    public function testFindByStatus()
    {
        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findReservationsByStatus')
            ->with($this->isType('string'));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($reservationRepository);

        $reservationManager = new BookReservationManager($doctrine);

        $reservationManager->findByStatus('status');
    }

    public function testFindUserReservationsByStatus()
    {
        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findUserReservationsByStatus')
            ->with($this->isInstanceOf(User::class), $this->isType('string'));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($reservationRepository);

        $reservationManager = new BookReservationManager($doctrine);

        $user = $this->createMock(User::class);
        $reservationManager->findUserReservationsByStatus($user, 'status');
    }

    /**
     * @dataProvider closedStatusProvider
     * @param $status
     */
    public function testUpdateStatusToReturnedOrCanceled($status)
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $reservationManager = new BookReservationManager($doctrine);

        $book = new Book(
            'ISBN',
            'title',
            $this->createMock(Author::class),
            123,
            'language',
            'publisher',
            new \DateTime(),
            1,
            'cover',
            'annotation'
        );
        $book->setReservedCopies(1);
        $reader = $this->createMock(User::class);
        $bookReservation = new BookReservation($book, $reader, new \DateTime(), new \DateTime());

        $reservationManager->updateStatus($bookReservation, $status);

        $this->assertEquals(
            $status, $bookReservation->getStatus(),
            'Status has been updated.'
        );
        $this->assertEquals(
            0, $book->getReservedCopies(),
            "Book's reserved copies have been updated."
        );
        $this->assertEquals(
            2, $book->getAvailableCopies(),
            "Book's available copies have been updated."
        );
    }

    public function closedStatusProvider()
    {
        return [
            ['returned'],
            ['canceled'],
        ];
    }

    /**
     * @dataProvider activeStatusProvider
     * @param $status
     */
    public function testUpdateStatusToReservedOrReading($status)
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $reservationManager = new BookReservationManager($doctrine);

        $book = new Book(
            'ISBN',
            'title',
            $this->createMock(Author::class),
            123,
            'language',
            'publisher',
            new \DateTime(),
            1,
            'cover',
            'annotation'
        );
        $book->setReservedCopies(1);
        $reader = $this->createMock(User::class);
        $bookReservation = new BookReservation($book, $reader, new \DateTime(), new \DateTime());

        $reservationManager->updateStatus($bookReservation, $status);

        $this->assertEquals(
            $status, $bookReservation->getStatus(),
            'Status has been updated.'
        );
    }

    public function activeStatusProvider()
    {
        return [
            ['reserved'],
            ['reading'],
        ];
    }

    public function testCheckReservationsForApproachingReturnDate()
    {
        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findReservationsWithApproachingEndDate')
            ->with($this->isInstanceOf(User::class));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($reservationRepository);

        $reservationManager = new BookReservationManager($doctrine);

        $user = $this->createMock(User::class);
        $reservationManager->checkReservationsForApproachingReturnDate($user);
    }

    public function testCheckReservationsForMissedReturnDate()
    {
        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findReservationsWithMissedEndDate')
            ->with($this->isInstanceOf(User::class));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($reservationRepository);

        $reservationManager = new BookReservationManager($doctrine);

        $user = $this->createMock(User::class);
        $reservationManager->checkReservationsForMissedReturnDate($user);
    }

    public function testCheckIfIsAvailableWithNoResult()
    {
        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findActiveReservationsByBookAndUser')
            ->with($this->isInstanceOf(Book::class), $this->isInstanceOf(User::class))
            ->willReturn(array());

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager');
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($reservationRepository);

        $reservationManager = new BookReservationManager($doctrine);

        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $result = $reservationManager->checkIfIsAvailable($book, $user);
        $this->assertTrue($result);
    }

    public function testCheckIfIsAvailableWithResult()
    {
        $reservation = $this->createMock(BookReservation::class);

        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findActiveReservationsByBookAndUser')
            ->with($this->isInstanceOf(Book::class), $this->isInstanceOf(User::class))
            ->willReturn(array($reservation));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager');
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($reservationRepository);

        $reservationManager = new BookReservationManager($doctrine);

        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $result = $reservationManager->checkIfIsAvailable($book, $user);
        $this->assertFalse($result);
    }
}
