<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 3:00 PM
 */

namespace App\Service;


use App\Entity\Author;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthorManager
{
    private $em;
    private $portraitDirectory;
    private $fileManager;
    private $repository;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        $portraitDirectory
    )
    {
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
        $this->portraitDirectory = $portraitDirectory;
        $this->repository = $doctrine->getRepository(Author::class);
    }

    /**
     * Converts an associative array of data to an instance of Author and saves it to the database.
     *
     * @param array $data Data about the author.
     *
     * @return void
     */
    public function createFromArray(array $data)
    {
        $author = new Author(
            $data['firstName'],
            $data['lastName'],
            $data['country'],
            $data['portrait']
        );

        $this->save($author);
    }

    /**
     * Converts an instance of Author to an associative array.
     *
     * @param Author $author
     *
     * @return array
     */
    public function createArrayFromAuthor(Author $author)
    {
        $portrait = $author->getPortrait();
        $photoPath = null === $portrait ? null : $this->portraitDirectory . '/' . $author->getPortrait();

        return [
            'firstName' => $author->getFirstName(),
            'lastName' => $author->getLastName(),
            'country' => $author->getCountry(),
            'portrait' => $photoPath === null ? null : $this->fileManager->createFileFromPath($photoPath),
        ];
    }

    /**
     * Changes an instance of Author using data from an associative array.
     * Removes the previous portrait if there was one when a new one has been provided.
     *
     * @param Author $author
     * @param array $data New data that will replace existing.
     *
     * @return void
     */
    public function updateAuthor(Author $author, array $data)
    {
        $photo = $data['portrait'];
        if ($photo instanceof UploadedFile) {
            $portrait = $author->getPortrait();
            $photoPath = $this->portraitDirectory . '/' . $portrait;
            !isset($portrait) ?: $this->fileManager->deleteFile($photoPath);

            $filename = $this->fileManager->upload($photo, $this->portraitDirectory);
            $author->setPortrait($filename);
        }

        $author->setFirstName($data['firstName']);
        $author->setLastName($data['lastName']);
        $author->setCountry($data['country']);

        $this->saveChanges();
    }

    /**
     * Looks for all authors in the database.
     *
     * @return Author[]|null
     */
    public function findAllAuthors()
    {
        return $this->repository->findAllAuthorsJoinedToBooks();
    }

    /**
     * Calls entity manager to make the instance managed and persistent and
     * to save all changes made to objects to the database.
     *
     * @param Author $author
     *
     * @return void
     */
    public function save(Author $author)
    {
        $this->em->persist($author);
        $this->saveChanges();
    }

    /**
     * Removes the author instance from the database.
     *
     * @param Author $author
     *
     * @return void
     */
    public function remove(Author $author)
    {
        $portrait = $author->getPortrait();
        null === $portrait ?: $this->fileManager->deleteFile($this->portraitDirectory . '/' . $portrait);

        $this->em->remove($author);
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
