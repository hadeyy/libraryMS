<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 4:38 PM
 */

namespace App\Service;


use App\Entity\Book;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookManager
{
    private $em;
    private $fileManager;
    private $coverDirectory;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        string $bookCoverDirectory
    ) {
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
        $this->coverDirectory = $bookCoverDirectory;
    }

    public function create(array $data): Book
    {
        return new Book(
            $data['ISBN'],
            $data['title'],
            $data['author'],
            $data['pages'],
            $data['language'],
            $data['publisher'],
            $data['publicationDate'],
            $data['availableCopies'],
            $data['cover'],
            $data['annotation']
        );
    }

    public function createArrayFromBook(Book $book): array
    {
        $photoPath = $this->coverDirectory . '/' . $book->getCover();

        return [
            'ISBN' => $book->getISBN(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'pages' => $book->getPages(),
            'language' => $book->getLanguage(),
            'publisher' => $book->getPublisher(),
            'publicationDate' => $book->getPublicationDate(),
            'availableCopies' => $book->getAvailableCopies(),
            'cover' => $this->fileManager->createFileFromPath($photoPath),
            'annotation' => $book->getAnnotation(),
            'genres' => $book->getGenres(),
        ];
    }

    private function uploadCover(Book $book)
    {
        $filename = $this->fileManager->upload($book->getCover(), $this->coverDirectory);
        $book->setCover($filename);
    }

    public function updateBook(Book $book, $data)
    {
        $photo = $data['cover'];
        if ($photo instanceof UploadedFile) {
            $photoPath = $this->coverDirectory . '/' . $book->getCover();
            $this->fileManager->deleteFile($photoPath);

            $filename = $this->fileManager->upload($photo, $this->coverDirectory);
            $book->setCover($filename);
        }

        $book->setISBN($data['ISBN']);
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setPages($data['pages']);
        $book->setLanguage($data['language']);
        $book->setPublisher($data['publisher']);
        $book->setPublicationDate($data['publicationDate']);
        $book->setAvailableCopies($data['availableCopies']);
        $book->setAnnotation($data['annotation']);

        $book->resetGenres();
        foreach ($data['genres'] as $genre) {
            $book->addGenre($genre);
        }

        $this->saveChanges();
    }

    public function remove(Book $book)
    {
        $this->fileManager->deleteFile($this->coverDirectory . '/' . $book->getCover());

        $this->em->remove($book);
        $this->saveChanges();
    }

    public function save(Book $book)
    {
        $this->uploadCover($book);

        $this->em->persist($book);
        $this->saveChanges();
    }

    public function saveChanges()
    {
        $this->em->flush();
    }
}
