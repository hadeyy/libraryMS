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

    public function create(array $data): Author
    {
        return new Author(
            $data['firstName'],
            $data['lastName'],
            $data['country'],
            $data['portrait']
        );
    }

    public function createArrayFromAuthor(Author $author): array
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
        $portrait = $author->getPortrait();
        null === $portrait ?: $this->fileManager->deleteFile($this->portraitDirectory . '/' . $portrait);

        $this->em->remove($author);
        $this->saveChanges();
    }
}
