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
    private $photoName;
    private $photoPath;
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

    public function create()
    {
        return new Book();
    }

    public function save(Book $book)
    {
        $this->uploadCover($book);

        $this->em->persist($book);
        $this->saveChanges();
    }

    private function uploadCover(Book $book)
    {
        $filename = $this->fileManager->upload($this->getCover($book), $this->coverDirectory);
        $this->setCover($book, $filename);
    }

    public function changePhotoFromPathToFile(Book $book)
    {
        $this->setPhotoName($this->getCover($book));
        $this->setPhotoPath($this->coverDirectory . '/' . $this->getPhotoName());
        $this->setCover($book, $this->fileManager->createFileFromPath($this->getPhotoPath()));
    }

    public function updateBook(Book $book)
    {
        $photo = $this->getCover($book);
        if ($photo instanceof UploadedFile) {
            $this->fileManager->deleteFile($this->getPhotoPath());
            $filename = $this->fileManager->upload($photo, $this->coverDirectory);
        } else {
            $filename = $this->getPhotoName();
        }

        $this->setCover($book, $filename);

        $this->saveChanges();
    }

    public function remove(Book $book)
    {
        $this->fileManager->deleteFile($this->coverDirectory . '/' . $this->getCover($book));

        $this->em->remove($book);
        $this->saveChanges();
    }

    public function getCover(Book $book)
    {
        return $book->getCover();
    }

    public function setCover(Book $book, $cover)
    {
        $book->setCover($cover);
    }

    public function saveChanges()
    {
        $this->em->flush();
    }

    public function setPhotoPath(string $photoPath)
    {
        $this->photoPath = $photoPath;
    }

    public function getPhotoPath()
    {
        return $this->photoPath;
    }

    public function setPhotoName(string $photoName)
    {
        $this->photoName = $photoName;
    }

    public function getPhotoName()
    {
        return $this->photoName;
    }
}
