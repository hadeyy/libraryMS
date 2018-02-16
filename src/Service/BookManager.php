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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookManager extends EntityManager
{
    private $coverDirectory;
    private $fileManager;
    private $activityManager;

    public function __construct(
        EntityManagerInterface $manager,
        ContainerInterface $container,
        FileManager $fileManager,
        ActivityManager $activityManager
    ) {
        parent::__construct($manager, $container);

        $this->coverDirectory = $container->getParameter('book_cover_directory');
        $this->fileManager = $fileManager;
        $this->activityManager = $activityManager;
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

    public function toggleFavorite(User $user, Book $book)
    {
        /** @var ArrayCollection $userFavorites */
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
}
