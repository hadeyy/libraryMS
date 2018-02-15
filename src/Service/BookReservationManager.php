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
use App\Repository\BookReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BookReservationManager extends EntityManager
{
    /** @var BookReservationRepository */
    private $repository;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        parent::__construct($manager, $container);

        $this->repository = $this->getRepository(BookReservation::class);
    }

    public function getBookReservation(int $id)
    {
        return $this->repository->find($id);
    }

    public function getByStatus(string $status)
    {
        return $this->repository->findReservationsByStatus($status);
    }

    public function updateStatus(int $id, string $status, \DateTime $updatedAt)
    {
        /** @var BookReservation $reservation */
        $reservation = $this->getBookReservation($id);
        $reservation->setStatus($status);
        $reservation->setUpdatedAt($updatedAt);

        if ($status === 'returned' || 'canceled') {
            $reservation->getFine() < 0 ?: $reservation->setFine(0);

            /** @var Book $book */
            $book = $reservation->getBook();
            $this->updateBook($book);
        }

        $this->em->flush();
    }

    private function updateBook(Book $book)
    {
        $availableCopies = $book->getAvailableCopies();
        $book->setAvailableCopies($availableCopies + 1);
        $reservedCopies = $book->getReservedCopies();
        $book->setReservedCopies($reservedCopies - 1);
    }
}
