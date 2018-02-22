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
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthorManager extends EntityManager
{
    private $photoName;
    private $photoPath;
    private $portraitDirectory;
    private $fileManager;

    public function __construct(
        EntityManagerInterface $manager,
        ContainerInterface $container,
        FileManager $fileManager
    )
    {
        parent::__construct($manager, $container);

        $this->portraitDirectory = $container->getParameter('author_portrait_directory');
        $this->fileManager = $fileManager;
    }

    public function getPaginatedCatalog(Author $author, int $currentPage, int $booksPerPage)
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $this->getRepository(Book::class);

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

    public function remove($entity)
    {
        null === $entity->getPortrait() ?: unlink($this->portraitDirectory . '/' . $entity->getPortrait());

        parent::remove($entity);
    }
}
