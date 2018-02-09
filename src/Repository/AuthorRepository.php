<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:06 AM
 */

namespace App\Repository;


use App\Entity\Author;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AuthorRepository extends EntityRepository
{
    public function findAuthorBooksAndPaginate(Author $author, $currentPage = 1, $limit = 18)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT a.books
            FROM App\Entity\Author a 
            WHERE a.id = :author'
        )->setParameter('author', $author->getId());

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
}
