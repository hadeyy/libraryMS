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

    /**
     * Creates a new instance of BookReservation and saves it to the database.
     *
     * @param Book $book Book that is being reserved.
     * @param User $reader User who made the book reservation.
     * @param \DateTime $dateFrom Start date of the book reservation.
     * @param \DateTime $dateTo End date of the book reservation.
     *
     * @return void
     */
    public function create(
        Book $book,
        User $reader,
        \DateTime $dateFrom,
        \DateTime $dateTo
    )
    {
        $bookReservation = new BookReservation($book, $reader, $dateFrom, $dateTo);

        $this->save($bookReservation);
    }

    /**
     * Looks for all book reservations that match the given status.
     *
     * @param string $status
     *
     * @return BookReservation[]|null
     */
    public function findByStatus(string $status)
    {
        return $this->repository->findReservationsByStatus($status);
    }

    /**
     * Looks for all book reservations that have been made by the given user
     * and match the given status.
     *
     * @param User $user
     * @param string $status
     *
     * @return BookReservation[]|null
     */
    public function findUserReservationsByStatus(User $user, string $status)
    {
        return $this->repository->findUserReservationsByStatus($user, $status);
    }

    /**
     * Changes the book reservation's status.
     *
     * @param BookReservation $reservation
     * @param string $status New status that will replace existing one.
     *
     * @return void
     */
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

    /**
     * Updates the book's number of available and reserved copies
     * after the book has been returned.
     *
     * @param Book $book
     *
     * @return void
     */
    private function updateBookAfterClosingReservation(Book $book)
    {
        $availableCopies = $book->getAvailableCopies();
        $book->setAvailableCopies($availableCopies + 1);
        $reservedCopies = $book->getReservedCopies();
        $book->setReservedCopies($reservedCopies - 1);
    }

    /**
     * Updates the book's number of available and reserved copies
     * after the book has been reserved.
     *
     * @param Book $book
     *
     * @return void
     */
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
     * Looks for book reservations that will have reached their end date in the next 3 days
     * and have not been closed yet.
     *
     * @param User $user
     *
     * @return BookReservation[]|null
     */
    public function checkReservationsForApproachingReturnDate(User $user)
    {
        return $this->repository->findReservationsWithApproachingEndDate($user);
    }

    /**
     * Looks for book reservations that have reached their end date
     * and have not been closed yet.
     *
     * @param User $user
     *
     * @return BookReservation[]|null
     */
    public function checkReservationsForMissedReturnDate(User $user)
    {
        return $this->repository->findReservationsWithMissedEndDate($user);
    }

    /**
     * Checks if the book is already reserved by the user.
     * Returns true if the user does not have an active book reservation with the book.
     * Returns false if the user has an active reservation with the book.
     *
     * @param Book $book
     * @param User $user
     *
     * @return bool
     */
    public function checkIfIsAvailable(Book $book, User $user)
    {
        $reservations = $this->repository->findActiveReservationsByBookAndUser($book, $user);

        return empty($reservations);
    }

    /**
     * Calls entity manager to make the instance managed and persistent and
     * to save all changes made to objects to the database.
     *
     * @param BookReservation $bookReservation
     *
     * @return void
     */
    public function save(BookReservation $bookReservation)
    {
        $this->updateBookAfterReservation($bookReservation->getBook());

        $this->em->persist($bookReservation);
        $this->saveChanges();
    }

    /**
     * Saves all changes made to objects to the database.
     *
     * @return void
     */
    public function saveChanges()
    {
        $this->em->flush();
    }
}
