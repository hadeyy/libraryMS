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
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthorManager
{
    private $doctrine;
    private $em;
    private $photoName;
    private $photoPath;
    private $portraitDirectory;
    private $fileManager;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        $portraitDirectory
    ) {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
        $this->portraitDirectory = $portraitDirectory;
    }

    public function getPaginatedCatalog(Author $author, int $currentPage, int $booksPerPage)
    {
        /** @todo get books from AuthorRepository */
        $bookRepository = $this->doctrine->getRepository(Book::class);

        return $bookRepository->findAuthorBooksAndPaginate($author, $currentPage, $booksPerPage);
    }

    public function create()
    {
        return new Author();
    }

    public function updateAuthor(Author $author)
    {
        $photo = $this->getPortrait($author);
        if ($photo instanceof UploadedFile) {
            $photoPath = $this->getPhotoPath();
            !isset($photoPath) ?: $this->fileManager->deleteFile($photoPath);
            $filename = $this->fileManager->upload($photo, $this->portraitDirectory);
        } else {
            $filename = $this->getPhotoName();
        }

        $this->setPortrait($author, $filename);

        $this->saveChanges();
    }

    public function changePhotoFromPathToFile(Author $author)
    {
        $portrait = $this->getPortrait($author);
        if (null !== $portrait) {
            $this->setPhotoName($portrait);
            $this->setPhotoPath($this->portraitDirectory . '/' . $this->getPhotoName());
            $this->setPortrait($author, $this->fileManager->createFileFromPath($this->getPhotoPath()));
        }
    }

    public function save(Author $author)
    {
        $this->em->persist($author);
        $this->saveChanges();
    }

    public function saveChanges()
    {
        $this->em->flush();
    }

    public function remove(Author $author)
    {
        $portrait = $this->getPortrait($author);
        null === $portrait ?: $this->fileManager->deleteFile($this->portraitDirectory . '/' . $portrait);

        $this->em->remove($author);
        $this->saveChanges();
    }

    public function setPhotoPath($path)
    {
        $this->photoPath = $path;
    }

    public function getPhotoPath()
    {
        return $this->photoPath;
    }

    public function setPhotoName($name)
    {
        $this->photoName = $name;
    }

    public function getPhotoName()
    {
        return $this->photoName;
    }

    public function setPortrait(Author $author, $portrait)
    {
        $author->setPortrait($portrait);
    }

    public function getPortrait(Author $author)
    {
        return $author->getPortrait();
    }
}
