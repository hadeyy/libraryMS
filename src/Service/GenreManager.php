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
use App\Repository\BookRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GenreManager extends EntityManager
{
    /** @var GenreRepository */
    private $repository;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        parent::__construct($manager, $container);

        $this->repository = $this->getRepository(Genre::class);
    }

    /**
     * @param int $id Genre ID.
     *
     * @return null|object
     */
    public function getGenre(int $id)
    {
        return $this->repository->find($id);
    }

    public function getPaginatedGenreCatalog(Genre $genre, int $currentPage, int $booksPerPage)
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $this->getRepository(Book::class);

        return $bookRepository->findGenreBooksAndPaginate($genre, $currentPage, $booksPerPage);
    }

    public function create()
    {
        return new Genre();
    }
}
