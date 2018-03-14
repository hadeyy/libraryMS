<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/1/2018
 * Time: 10:43 AM
 */

namespace App\Tests\Service;


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
    public function testCreateAddsDataToReservation()
    {
        $book = $this->createMock(Book::class);
        $reader = $this->createMock(User::class);
        $dates = [
            'dateFrom' => new \DateTime(),
            'dateTo' => new \DateTime(),
        ];

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $reservation = $reservationManager->create($book, $reader, $dates);

        $this->assertTrue(
            $reservation instanceof BookReservation,
            'Result is an instance of BookReservation class.'
        );
        $this->assertEquals($book, $reservation->getBook());
        $this->assertEquals($reader, $reservation->getReader());
        $this->assertEquals($dates['dateFrom'], $reservation->getDateFrom());
        $this->assertEquals($dates['dateTo'], $reservation->getDateTo());
    }

    public function testFindByStatusCallsBookReservationRepository()
    {
        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findReservationsByStatus')
            ->with($this->isType('string'))
            ->willReturn(null);
        $reservationRepository->expects($this->once())
            ->method('findUserReservationsByStatus')
            ->with($this->isInstanceOf(User::class), $this->isType('string'))
            ->willReturn(null);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager');
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($reservationRepository);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['findByStatus', 'findUserReservationsByStatus'])
            ->getMock();

        $user = $this->createMock(User::class);

        $reservationManager->findByStatus('status');
        $reservationManager->findUserReservationsByStatus($user, 'status');
    }


    /**
     * @dataProvider closedStatusProvider
     * @param $status
     */
    public function testUpdateStatusToReturnedOrCanceled($status)
    {
        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['updateStatus'])
            ->getMock();
        $reservationManager->expects($this->once())
            ->method('saveChanges');

        $bookReservation = $this->createMock(BookReservation::class);
        $reservationManager->updateStatus($bookReservation, $status, new \DateTime());
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
        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['updateStatus'])
            ->getMock();
        $reservationManager->expects($this->once())
            ->method('saveChanges');

        $bookReservation = $this->createMock(BookReservation::class);
        $reservationManager->updateStatus($bookReservation, $status, new \DateTime());
    }

    public function activeStatusProvider()
    {
        return [
            ['reserved'],
            ['reading'],
        ];
    }

    /**
     * @dataProvider closedStatusProvider
     * @param $status
     */
    public function testUpdateStatusToReturnedOrCanceledResetsFine($status)
    {
        $reservation = new BookReservation(
            $this->createMock(Book::class),
            $this->createMock(User::class),
            new \DateTime(),
            new \DateTime()
        );
        $reservation->setFine(1.5);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['updateStatus'])
            ->getMock();

        $this->assertEquals(1.5, $reservation->getFine());
        $reservationManager->updateStatus($reservation, $status, new \DateTime());
        $this->assertEquals(0, $reservation->getFine());
    }

    public function testCreateAddsDataToBookReservation()
    {
        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);
        $date = new \DateTime();
        $dates = [
            'dateFrom' => $date,
            'dateTo' => $date,
        ];

        $reservation = $reservationManager->create($book, $user, $dates);

        $this->assertTrue(
            $reservation instanceof BookReservation,
            'Result is an instance of BookReservation class.'
        );
        $this->assertEquals($book, $reservation->getBook(), 'Reservation book matches expected.');
        $this->assertEquals($user, $reservation->getReader(), 'Reservation reader matches expected.');
        $this->assertEquals($date, $reservation->getDateFrom(), 'Date matches expected.');
        $this->assertEquals($date, $reservation->getDateTo(), 'Date matches expected.');

        return $reservation;
    }

    /**
     * @depends testCreateAddsDataToBookReservation
     * @param BookReservation $reservation
     */
    public function testSavingMethodsCallEntityManager(BookReservation $reservation)
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(BookReservation::class));
        $entityManager->expects($this->exactly(2))
            ->method('flush');

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['save', 'saveChanges'])
            ->getMock();

        $reservationManager->save($reservation);
        $reservationManager->saveChanges();
    }

    public function testCheckReservationsCallBookReservationRepository()
    {
        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findReservationsWithApproachingEndDate')
            ->with($this->isInstanceOf(User::class))
            ->willReturn(null);
        $reservationRepository->expects($this->once())
            ->method('findReservationsWithMissedEndDate')
            ->with($this->isInstanceOf(User::class))
            ->willReturn(null);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager');
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($reservationRepository);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept([
                'checkReservationsForApproachingReturnDate',
                'checkReservationsForMissedReturnDate',
            ])
            ->getMock();

        $user = $this->createMock(User::class);

        $reservationManager->checkReservationsForApproachingReturnDate($user);
        $reservationManager->checkReservationsForMissedReturnDate($user);
    }

    public function testCheckIfIsReservedWithNoResult()
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

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['checkIfIsReserved'])
            ->getMock();

        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $result = $reservationManager->checkIfIsReserved($book, $user);
        $this->assertFalse($result);
    }

    public function testCheckIfIsReservedWithResult()
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

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['checkIfIsReserved'])
            ->getMock();

        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $result = $reservationManager->checkIfIsReserved($book, $user);
        $this->assertTrue($result);
    }
}
