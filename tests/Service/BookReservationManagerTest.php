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
    public function testUpdateStatusToReturnedOrCanceledCallsOtherClassMethods($status)
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
        $reservationManager->expects($this->once())
            ->method('getFine')
            ->with($this->isInstanceOf(BookReservation::class))
            ->willReturn(1);
        $reservationManager->expects($this->once())
            ->method('setFine')
            ->with($this->isInstanceOf(BookReservation::class), $this->isType('float'));
        $reservationManager->expects($this->once())
            ->method('getBook')
            ->with($this->isInstanceOf(BookReservation::class))
            ->willReturn(new Book());
        $reservationManager->expects($this->once())
            ->method('saveChanges');

        $reservationManager->updateStatus(new BookReservation(new Book(), new User()), $status, new \DateTime());
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
    public function testUpdateStatusToReservedOrReadingDoesNotCallMethodsInIfBody($status)
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

        $reservationManager->updateStatus(new BookReservation(new Book(), new User()), $status, new \DateTime());
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
        $reservation = new BookReservation(new Book(), new User());
        $reservation->setFine(1.5);

        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['updateStatus', 'getFine', 'setFine', 'getBook'])
            ->getMock();

        $this->assertEquals(1.5, $reservation->getFine());
        $reservationManager->updateStatus($reservation, $status, new \DateTime());
        $this->assertEquals(0, $reservation->getFine());
    }

    public function testCreateAddsBookAndUserToBookReservations()
    {
        $reservationManager = $this->getMockBuilder(BookReservationManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $book = new Book();
        $user = new User();

        $reservation = $reservationManager->create($book, $user);

        $this->assertTrue(
            $reservation instanceof BookReservation,
            'Result is an instance of BookReservation class.'
        );
        $this->assertEquals($book, $reservation->getBook(), 'Reservation book matches expected.');
        $this->assertEquals($user, $reservation->getReader(), 'Reservation reader matches expected.');
    }

    public function testSavingMethodsCallEntityManagerMethods()
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
            ->setMethodsExcept(['save', 'saveChanges', 'getBook'])
            ->getMock();

        $reservationManager->save(new BookReservation(new Book(), new User()));
        $reservationManager->saveChanges();
    }
}
