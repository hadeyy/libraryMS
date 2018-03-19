<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 3:20 PM
 */

namespace App\Service;


use App\Entity\Book;
use App\Entity\Genre;
use Doctrine\Common\Persistence\ManagerRegistry;

class GenreManager
{
    private $doctrine;
    private $em;
    private $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(Genre::class);
    }

    public function getPaginatedCatalog(Genre $genre, int $currentPage, int $booksPerPage)
    {
        /** @todo get books from GenreRepository */
        $bookRepository = $this->doctrine->getRepository(Book::class);

        return $bookRepository->findGenreBooksAndPaginate($genre, $currentPage, $booksPerPage);
    }

    public function create(array $data): Genre
    {
        return new Genre($data['name']);
    }

    public function save(Genre $genre)
    {
        $this->em->persist($genre);
        $this->em->flush();
    }
}
