<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:07 AM
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
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
