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
    ) {
        $bookReservation = new BookReservation($book, $reader, $dates['dateFrom'], $dates['dateTo']);

        $this->save($bookReservation);
    }

    public function findByStatus(string $status)
    {
        return $this->repository->findReservationsByStatus($status);
    }

    public function findUserReservationsByStatus(User $user, string $string)
    {
        return $this->repository->findUserReservationsByStatus($user, $string);
    }

    public function updateStatus(BookReservation $reservation, string $status)
    {
        $reservation->setStatus($status);
        $reservation->setUpdatedAt(new \DateTime());

        if ($status === 'returned' || $status === 'canceled') {
            0 >= $reservation->getFine() ?: $reservation->setFine(0);

            $book = $reservation->getBook();
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

    public function checkIfIsAvailable(Book $book, User $user)
    {
        $reservations = $this->repository->findActiveReservationsByBookAndUser($book, $user);

        return empty($reservations);
    }

    public function save(BookReservation $bookReservation)
    {
        $this->updateBookAfterReservation($bookReservation->getBook());

        $this->em->persist($bookReservation);
        $this->saveChanges();
    }

    public function saveChanges()
    {
        $this->em->flush();
    }
}
