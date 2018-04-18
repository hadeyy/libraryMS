<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:07 AM
 */

namespace App\Repository;


use App\Entity\Book;
use App\Entity\BookReservation;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class BookReservationRepository extends EntityRepository
{
    /**
     * Finds all book reservations by status
     * and orders results by time updated.
     *
     * @param string $status
     *
     * @return BookReservation[]|null
     */
    public function findReservationsByStatus(string $status)
    {
        return $this->createQueryBuilder('br')
            ->select('br, b, u, a')
            ->innerJoin('br.book', 'b')
            ->innerJoin('br.reader', 'u')
            ->innerJoin('b.author', 'a')
            ->where('br.status = :status')
            ->setParameter('status', $status)
            ->orderBy('br.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all book reservations by user and status
     * and orders results by time updated.
     *
     * @param User $user
     * @param string $status
     *
     * @return BookReservation[]|null
     */
    public function findUserReservationsByStatus(User $user, string $status)
    {
        return $this->createQueryBuilder('br')
            ->select('br, b, a')
            ->innerJoin('br.book', 'b')
            ->innerJoin('b.author', 'a')
            ->where('br.status = :status')
            ->andWhere('br.reader = :user')
            ->setParameters(['status' => $status, 'user' => $user])
            ->orderBy('br.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all active book reservations by user
     * where end date is in less than 3 days.
     *
     * @param User $user
     *
     * @return BookReservation[]|null
     */
    public function findReservationsWithApproachingEndDate(User $user)
    {
        return $this->createQueryBuilder('br')
            ->select('br, b, a')
            ->innerJoin('br.book', 'b')
            ->innerJoin('b.author', 'a')
            ->where('br.status = :status')
            ->andWhere('DATE_DIFF(br.dateTo, CURRENT_DATE()) < 3 AND DATE_DIFF(br.dateTo, CURRENT_DATE()) >= 0')
            ->andWhere('br.reader = :user')
            ->setParameters(['user' => $user, 'status' => 'reading'])
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all active book reservations by user
     * where end date was in any day before today.
     *
     * @param User $user
     *
     * @return BookReservation[]|null
     */
    public function findReservationsWithMissedEndDate(User $user)
    {
        return $this->createQueryBuilder('br')
            ->select('br, b, a')
            ->innerJoin('br.book', 'b')
            ->innerJoin('b.author', 'a')
            ->where('br.status = :status')
            ->andWhere('br.dateTo < CURRENT_DATE()')
            ->andWhere('br.reader = :user')
            ->setParameters(['user' => $user, 'status' => 'reading'])
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all active book reservations by user and book.
     *
     * @param Book $book
     * @param User $user
     *
     * @return BookReservation[]|null
     */
    public function findActiveReservationsByBookAndUser(Book $book, User $user)
    {
        return $this->createQueryBuilder('br')
            ->andwhere('br.book = :book')
            ->andWhere('br.reader = :reader')
            ->andWhere('br.status IN (:statuses)')
            ->setParameters([
                'book' => $book,
                'reader' => $user,
                'statuses' => ['reserved', 'reading'],
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all active book reservations by book and orders results by reservation end date.
     *
     * @param Book $book
     *
     * @return BookReservation[]|null
     */
    public function findActiveReservationsByBook(Book $book)
    {
        return $this->createQueryBuilder('br')
            ->andwhere('br.book = :book')
            ->andWhere('br.status IN (:statuses)')
            ->setParameters([
                'book' => $book,
                'statuses' => ['reserved', 'reading'],
            ])
            ->orderBy('br.dateTo', 'ASC')
            ->getQuery()
            ->getResult();
    }

}
