<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:07 AM
 */

namespace App\Repository;


use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class BookReservationRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return mixed
     */
    public function findCurrentReservations(User $user)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT r 
            FROM App\Entity\BookReservation r 
            WHERE r.reader = :reader AND r.status = :status
            ORDER BY r.dateFrom ASC'
        )->setParameters(['reader' => $user, 'status' => 'reading']);

        return $query->execute();
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function findPastReservations(User $user)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT r 
            FROM App\Entity\BookReservation r 
            WHERE r.reader = :reader AND r.status = :status
            ORDER BY r.dateFrom ASC'
        )->setParameters(['reader' => $user, 'status' => 'returned']);

        return $query->execute();
    }
}
