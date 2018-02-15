<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 3:00 PM
 */

namespace App\Service;


use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthorManager extends EntityManager
{
    /** @var AuthorRepository */
    private $repository;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        parent::__construct($manager, $container);

        $this->repository = $this->getRepository(Author::class);
    }

    /**
     * @param int $id Author ID.
     *
     * @return null|object
     */
    public function getAuthor(int $id)
    {
        return $this->repository->find($id);
    }

    public function getPaginatedAuthorCatalog(Author $author, int $currentPage, int $booksPerPage)
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $this->getRepository(Book::class);

        return $bookRepository->findAuthorBooksAndPaginate($author, $currentPage, $booksPerPage);
    }

    public function create()
    {
        return new Author();
    }
}
