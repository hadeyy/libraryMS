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
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthorManager
{
    private $doctrine;
    private $em;
    private $photoName;
    private $photoPath;
    private $portraitDirectory;
    private $fileManager;

    public function __construct(ManagerRegistry $doctrine, FileManager $fileManager, $portraitDirectory)
    {
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
        $photo = $author->getPortrait();
        if ($photo instanceof UploadedFile) {
            /** @todo move to FileManager */
            !isset($this->photoPath) ?: unlink($this->photoPath);
            $filename = $this->fileManager->upload($photo, $this->portraitDirectory);
        } else {
            $filename = $this->photoName;
        }

        $author->setPortrait($filename);

        $this->em->flush();
    }

    public function changePhotoFromPathToFile(Author $author)
    {
        if (null !== $author->getPortrait()) {
            $this->photoName = $author->getPortrait();
            $this->photoPath = $this->portraitDirectory . '/' . $this->photoName;
            $author->setPortrait($this->fileManager->createFileFromPath($this->photoPath));
        }
    }

    public function save(Author $author)
    {
        $this->em->persist($author);
        $this->em->flush();
    }

    public function remove(Author $author)
    {
        /** @todo move to FileManager */
        null === $author->getPortrait() ?: unlink($this->portraitDirectory . '/' . $author->getPortrait());

        $this->em->remove($author);
        $this->em->flush();
    }
}
