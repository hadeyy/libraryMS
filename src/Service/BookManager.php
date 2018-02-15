<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 4:38 PM
 */

namespace App\Service;


use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookManager extends EntityManager
{
    private $coverDirectory;
    private $fileManager;

    public function __construct(
        EntityManagerInterface $manager,
        ContainerInterface $container,
        FileManager $fileManager
    ) {
        parent::__construct($manager, $container);

        $this->coverDirectory = $container->getParameter('book_cover_directory');
        $this->fileManager = $fileManager;
    }

    public function create()
    {
        return new Book();
    }

    public function submit(Book $book)
    {
        $filename = $this->uploadCover($book->getCover(), $this->coverDirectory, $this->fileManager);
        $book->setCover($filename);
        $this->save($book);
    }

    private function uploadCover(UploadedFile $cover, string $path, FileManager $fileManager)
    {
        return $fileManager->upload($cover, $path);
    }
}
