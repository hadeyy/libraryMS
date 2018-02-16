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
use App\Repository\BookRepository;

class AuthorManager extends EntityManager
{
    public function getPaginatedCatalog(Author $author, int $currentPage, int $booksPerPage)
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
