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
use Doctrine\Common\Collections\ArrayCollection;
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

    public function testGetByStatusCallsBookReservationRepository()
    {
        $reservations = new ArrayCollection();

        $reservationRepository = $this->createMock(BookReservationRepository::class);
        $reservationRepository->expects($this->once())
            ->method('findReservationsByStatus')
            ->with($this->isType('string'))
            ->willReturn($reservations);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager');
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($reservationRepository);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['getByStatus'])
            ->getMock();

        $result = $reservationManager->getByStatus('status');

        $this->assertTrue(
            $result instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertEquals($reservations, $result, 'Retrieved result matches expected.');
    }


    /**
     * @dataProvider closedStatusProvider
     * @param $status
     */
    public function testUpdateStatusToReturnedOrCanceled($status)
    {
        $book = $this->createMock(Book::class);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept([
                'updateStatus',
                'setStatus',
                'setUpdatedAt',
                'getFine',
                'setFine',
            ])
            ->getMock();
        $reservationManager->expects($this->once())
            ->method('getBook')
            ->with($this->isInstanceOf(BookReservation::class))
            ->willReturn($book);
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
            ->method('setStatus')
            ->with($this->isInstanceOf(BookReservation::class), $this->isType('string'));
        $reservationManager->expects($this->once())
            ->method('setUpdatedAt')
            ->with(
                $this->isInstanceOf(BookReservation::class),
                $this->isInstanceOf(\DateTime::class)
            );
        $reservationManager->expects($this->exactly(0))
            ->method('getFine');
        $reservationManager->expects($this->exactly(0))
            ->method('setFine');
        $reservationManager->expects($this->exactly(0))
            ->method('getBook');
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
            ->setMethodsExcept(['updateStatus', 'getFine', 'setFine', 'getBook'])
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
        $entityManager->expects($this->exactly(2))
            ->method('clear');

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['save', 'saveChanges', 'getBook'])
            ->getMock();

        $reservationManager->save($reservation);
        $reservationManager->saveChanges();
    }
}
