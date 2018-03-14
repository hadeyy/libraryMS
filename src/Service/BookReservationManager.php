<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 5:15 PM
 */

namespace App\Service;


use App\Entity\Book;
use App\Entity\BookReservation;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

class BookReservationManager
{
    private $em;
    private $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(BookReservation::class);
    }

    public function create(
        Book $book,
        User $reader,
        array $dates
    ): BookReservation {
        return new BookReservation($book, $reader, $dates['dateFrom'], $dates['dateTo']);
    }

    public function findByStatus(string $status)
    {
        return $this->repository->findReservationsByStatus($status);
    }

    public function findUserReservationsByStatus(User $user, string $string)
    {
        return $this->repository->findUserReservationsByStatus($user, $string);
    }

    public function updateStatus(BookReservation $reservation, string $status, \DateTime $updatedAt)
    {
        $reservation->setStatus($status);
        $reservation->setUpdatedAt($updatedAt);

        if ($status === 'returned' || $status === 'canceled') {
            0 >= $reservation->getFine() ?: $reservation->setFine(0);

            $book = $this->getBook($reservation);
            $this->updateBookAfterClosingReservation($book);
        }

        $this->saveChanges();
    }

    private function updateBookAfterClosingReservation(Book $book)
    {
        $availableCopies = $book->getAvailableCopies();
        $book->setAvailableCopies($availableCopies + 1);
        $reservedCopies = $book->getReservedCopies();
        $book->setReservedCopies($reservedCopies - 1);
    }

    private function updateBookAfterReservation(Book $book)
    {
        $availableCopies = $book->getAvailableCopies();
        $book->setAvailableCopies($availableCopies - 1);
        $reservedCopies = $book->getReservedCopies();
        $book->setReservedCopies($reservedCopies + 1);
        $timesBorrowed = $book->getTimesBorrowed();
        $book->setTimesBorrowed($timesBorrowed + 1);
    }

    /**
     * @param User $user
     *
     * @return BookReservation[]|null
     */
    public function checkReservationsForApproachingReturnDate(User $user)
    {
        return $this->repository->findReservationsWithApproachingEndDate($user);
    }

    /**
     * @param User $user
     *
     * @return BookReservation[]|null
     */
    public function checkReservationsForMissedReturnDate(User $user)
    {
        return $this->repository->findReservationsWithMissedEndDate($user);
    }

    public function checkIfIsReserved(Book $book, User $user)
    {
        $reservations = $this->repository->findActiveReservationsByBookAndUser($book, $user);

        return empty($reservations) ? false : true;
    }

    private function getBook(BookReservation $bookReservation)
    {
        return $bookReservation->getBook();
    }

    public function save(BookReservation $bookReservation)
    {
        $this->updateBookAfterReservation($this->getBook($bookReservation));

        $this->em->persist($bookReservation);
        $this->saveChanges();
    }

    public function saveChanges()
    {
        $this->em->flush();
    }
}
