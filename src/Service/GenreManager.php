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

    /**
     * Creates a new instance of Genre and saves it to the database.
     *
     * @param string $name Name of the genre.
     *
     * @return void
     */
    public function create(string $name)
    {
        $genre = new Genre($name);

        $this->save($genre);
    }

    /**
     * Converts an instance of Genre to an associative array.
     *
     * @param Genre $genre
     *
     * @return array
     */
    public function createArrayFromGenre(Genre $genre)
    {
        return ['name' => $genre->getName()];
    }

    /**
     * Changes genre's name.
     *
     * @param Genre $genre
     * @param string $newName
     *
     * @return void
     */
    public function changeName(Genre $genre, string $newName)
    {
        $genre->setName($newName);
        $this->saveChanges();
    }

    /**
     * Removes the genre instance from the database.
     *
     * @param Genre $genre
     *
     * @return void
     */
    public function remove(Genre $genre)
    {
        $this->em->remove($genre);
        $this->saveChanges();
    }

    /**
     * Looks for all genres in the database.
     *
     * @return Genre[]|null
     */
    public function findAllGenres()
    {
        return $this->repository->findAllGenresJoinedToBooks();
    }

    /**
     * Calls entity manager to make the instance managed and persistent and
     * to save all changes made to objects to the database.
     *
     * @param Genre $genre
     *
     * @return void
     */
    public function save(Genre $genre)
    {
        $this->em->persist($genre);
        $this->saveChanges();
    }

    /**
     * Saves all changes made to objects to the database.
     *
     * @return void
     */
    public function saveChanges()
    {
        $this->em->flush();
    }
}
