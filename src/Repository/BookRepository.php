<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:07 AM
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BookRepository extends EntityRepository
{
    public function findAllAndPaginate($currentPage = 1, $limit = 18)
    {
        $query = $this->createQueryBuilder('b')
            ->orderBy('b.title', 'ASC')
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }

    private function paginate($query, $page, $limit)
    {
        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }

    public function findAllOrderedByTimesBorrowed()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT b
            FROM App\Entity\Book b
            ORDER BY b.timesBorrowed DESC'
        );

        return $query->execute();
    }

    public function findAllOrderedByPublicationDate()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT b
            FROM App\Entity\Book b
            ORDER BY b.publicationDate DESC'
        );

        return $query->execute();
    }

}
