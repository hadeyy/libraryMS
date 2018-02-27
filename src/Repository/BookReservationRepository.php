<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:07 AM
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class BookReservationRepository extends EntityRepository
{
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

}
