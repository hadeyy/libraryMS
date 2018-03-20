<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 3:20 PM
 */

namespace App\Service;


use App\Entity\Genre;
use Doctrine\Common\Persistence\ManagerRegistry;

class GenreManager
{
    private $em;
    private $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(Genre::class);
    }

    public function create(array $data)
    {
        $genre = new Genre($data['name']);

        $this->save($genre);
    }

    public function findAllGenres()
    {
        return $this->repository->findAllGenresJoinedToBooks();
    }

    public function save(Genre $genre)
    {
        $this->em->persist($genre);
        $this->em->flush();
    }
}
