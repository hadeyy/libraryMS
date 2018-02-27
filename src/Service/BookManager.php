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
    private $photoName;
    private $photoPath;
    private $em;
    private $fileManager;
    private $activityManager;
    private $coverDirectory;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        ActivityManager $activityManager,
        string $bookCoverDirectory
    ) {
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
        $this->activityManager = $activityManager;
        $this->coverDirectory = $bookCoverDirectory;
    }

    public function create()
    {
        return new Book();
    }

    public function submit(Book $book)
    {
        $filename = $this->uploadCover($book->getCover(), $this->coverDirectory);
        $book->setCover($filename);
        $this->save($book);
    }

    private function uploadCover(UploadedFile $cover, string $path)
    {
        return $this->fileManager->upload($cover, $path);
    }

    public function toggleFavorite(User $user, Book $book)
    {
        $userFavorites = $user->getFavorites();
        /** @var bool $isAFavorite */
        $isAFavorite = $userFavorites->contains($book);
        $action = $isAFavorite ?: 'add';

        if ('add' === $action) {
            $user->addFavorite($book);
            $this->activityManager->log($user, $book, 'Added a book to favorites');
        } else {
            $user->removeFavorite($book);
            $this->activityManager->log($user, $book, 'Removed a book from favorites');
        }
    }

    public function changePhotoFromPathToFile(Book $book)
    {
        $this->photoName = $book->getCover();
        $this->photoPath = $this->coverDirectory . '/' . $this->photoName;
        $book->setCover($this->fileManager->createFileFromPath($this->photoPath));
    }

    public function updateBook(Book $book)
    {
        $photo = $book->getCover();
        if ($photo instanceof UploadedFile) {
            unlink($this->photoPath);
            $filename = $this->fileManager->upload($photo, $this->coverDirectory);
        } else {
            $filename = $this->photoName;
        }

        $book->setCover($filename);

        $this->em->flush();
    }

    public function save(Book $book)
    {
        $this->em->persist($book);
        $this->em->flush();
    }

    public function remove(Book $book)
    {
        unlink($this->coverDirectory . '/' . $book->getCover());

        $this->em->remove($book);
        $this->em->flush();
    }
}
