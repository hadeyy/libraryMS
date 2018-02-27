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

    public function getByStatus(string $status)
    {
        return $this->repository->findReservationsByStatus($status);
    }

    public function updateStatus(BookReservation $reservation, string $status, \DateTime $updatedAt)
    {
        $reservation->setStatus($status);
        $reservation->setUpdatedAt($updatedAt);

        if ($status === 'returned' || 'canceled') {
            $reservation->getFine() < 0 ?: $reservation->setFine(0);

            $book = $reservation->getBook();
            $this->updateBook($book);
        }

        $this->saveChanges();
    }

    private function updateBook(Book $book)
    {
        $availableCopies = $book->getAvailableCopies();
        $book->setAvailableCopies($availableCopies + 1);
        $reservedCopies = $book->getReservedCopies();
        $book->setReservedCopies($reservedCopies - 1);
    }

    public function create(Book $book, User $user)
    {
        $reservation = new BookReservation();
        $reservation->setBook($book);
        $reservation->setReader($user);

        return $reservation;
    }

    public function reserve(BookReservation $reservation, Book $book, User $user)
    {
        $book->addReservation($reservation);
        $user->addBookReservation($reservation);

        $this->updateBookAfterReservation($book);
        $this->save($reservation);
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

    public function saveChanges()
    {
        $this->em->flush();
    }

    public function save(BookReservation $bookReservation)
    {
        $this->em->persist($bookReservation);
        $this->em->flush();
    }
}
