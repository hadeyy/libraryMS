<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/20/2018
 * Time: 3:42 PM
 */

namespace App\Tests\Service;


use App\Entity\Author;
use App\Entity\Genre;
use App\Repository\BookRepository;
use App\Service\CatalogManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CatalogManagerTest extends WebTestCase
{
    private $bookRepository;
    private $catalogManager;

    public function setUp()
    {
        $this->bookRepository = $this->createMock(BookRepository::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($this->bookRepository);

        $this->catalogManager = new CatalogManager($doctrine);
    }

    public function testGetPaginatedBookCatalog()
    {
        $this->bookRepository->expects($this->once())
            ->method('findAllAndPaginate')
            ->with($this->isType('int'), $this->isType('int'));

        $this->catalogManager->getPaginatedBookCatalog(1, 1);
    }

    public function testGetPaginatedAuthorCatalog()
    {
        $this->bookRepository->expects($this->once())
            ->method('findAuthorBooksAndPaginate')
            ->with(
                $this->isInstanceOf(Author::class),
                $this->isType('int'),
                $this->isType('int')
            );

        $author = $this->createMock(Author::class);
        $this->catalogManager->getPaginatedAuthorCatalog($author, 1, 1);
    }

    public function testGetPaginatedGenreCatalog()
    {
        $this->bookRepository->expects($this->once())
            ->method('findGenreBooksAndPaginate')
            ->with(
                $this->isInstanceOf(Genre::class),
                $this->isType('int'),
                $this->isType('int')
            );

        $genre = $this->createMock(Genre::class);
        $this->catalogManager->getPaginatedGenreCatalog($genre, 1, 1);
    }

    public function testGetMaxPages()
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->expects($this->once())
            ->method('count')
            ->willReturn(23);

        $result = $this->catalogManager->getMaxPages($paginator, 7);
        $this->assertEquals(4, $result, 'Retrieved result matches expected.');
    }
}
