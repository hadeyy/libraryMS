<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 4:38 PM
 */

namespace App\Service;


use App\Entity\Book;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookManager
{
    private $em;
    private $fileManager;
    private $coverDirectory;
    private $repository;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        string $bookCoverDirectory
    )
    {
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
        $this->coverDirectory = $bookCoverDirectory;
        $this->repository = $doctrine->getRepository(Book::class);
    }

    /**
     * Converts an associative array of data to an instance of Book and saves it to the database.
     *
     * @param array $data Data about the book.
     *
     * @return Book
     */
    public function createFromArray(array $data)
    {
        $book = new Book(
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

        $this->save($book);

        return $book;
    }

    /**
     * Converts an instance of Book to an associative array.
     *
     * @param Book $book
     *
     * @return array
     */
    public function createArrayFromBook(Book $book)
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

    /**
     * Uploads the book's cover to cover directory and
     * sets the created filename as the cover for the book.
     *
     * @param Book $book
     *
     * @return void
     */
    private function uploadCover(Book $book): void
    {
        $filename = $this->fileManager->upload($book->getCover(), $this->coverDirectory);
        $book->setCover($filename);
    }

    /**
     * Changes an instance of Book using data from an associative array.
     * Removes the previous cover photo if there was one when a new one has been provided.
     *
     * @param Book $book
     * @param array $data New data that will replace existing.
     *
     * @return void
     */
    public function updateBook(Book $book, array $data)
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

    /**
     * Looks for all books ordered by how many times they have been borrowed.
     *
     * @return Book[]|null
     */
    public function getPopularBooks()
    {
        return $this->repository->findAllOrderedByTimesBorrowed();
    }

    /**
     * Looks for all books ordered by their publication date.
     *
     * @return Book[]|null
     */
    public function getNewestBooks()
    {
        return $this->repository->findAllOrderedByPublicationDate();
    }

    /**
     * Based on whether or not the given book is in the user's favorites
     * adds or removes the book from them.
     *
     * @param Book $book
     * @param User $user
     *
     * @return string Made activity description.
     */
    public function toggleFavorite(Book $book, User $user)
    {
        $favorites = $user->getFavorites();
        $isAFavorite = $favorites->contains($book);

        if ($isAFavorite) {
            $user->removeFavorite($book);
            $this->saveChanges();

            return 'Removed a book from favorites';
        } else {
            $user->addFavorite($book);
            $this->saveChanges();

            return 'Added a book to favorites';
        }
    }

    /**
     * Removes the book instance from the database.
     *
     * @param Book $book
     *
     * @return void
     */
    public function remove(Book $book)
    {
        $this->fileManager->deleteFile($this->coverDirectory . '/' . $book->getCover());

        $this->em->remove($book);
        $this->saveChanges();
    }

    /**
     * Calls entity manager to make the instance managed and persistent and
     * to save all changes made to objects to the database.
     *
     * @param Book $book
     *
     * @return void
     */
    public function save(Book $book)
    {
        $this->uploadCover($book);

        $this->em->persist($book);
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
