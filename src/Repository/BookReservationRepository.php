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
    public function findReservationsByStatus(string $status)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT r 
            FROM App\Entity\BookReservation r
            WHERE r.status = :status
            ORDER BY r.updatedAt DESC'
        )->setParameter('status', $status);

        return $query->execute();
    }

}
