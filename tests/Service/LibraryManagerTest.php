<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/28/2018
 * Time: 3:43 PM
 */

namespace App\Tests\Service;


use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\GenreRepository;
use App\Service\LibraryManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LibraryManagerTest extends WebTestCase
{
    public function testServiceMethodsCallBookRepository()
    {
        $arrayCollection = new ArrayCollection();
        $paginator = $this->createMock(Paginator::class);

        $bookRepository = $this->getMockBuilder(BookRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bookRepository->expects($this->once())
            ->method('findAllOrderedByTimesBorrowed')
            ->willReturn($arrayCollection);
        $bookRepository->expects($this->once())
            ->method('findAllOrderedByPublicationDate')
            ->willReturn($arrayCollection);
        $bookRepository->expects($this->once())
            ->method('findAllAndPaginate')
            ->with($this->isType('int'), $this->isType('int'))
            ->willReturn($paginator);

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->exactly(3))
            ->method('getRepository')
            ->willReturn($bookRepository);

        $libraryManager = $this->getMockBuilder(LibraryManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept([
                'getPopularBooks',
                'getNewestBooks',
                'getPaginatedBookCatalog'
            ])
            ->getMock();

        $popularResult = $libraryManager->getPopularBooks();
        $newestResult = $libraryManager->getNewestBooks();
        $paginatedResult = $libraryManager->getPaginatedBookCatalog(1, 5);

        $this->assertTrue(
            $popularResult instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertTrue(
            $newestResult instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
        $this->assertTrue(
            $paginatedResult instanceof Paginator,
            'Result is an instance of Paginator.'
        );
    }

    public function testGetAllAuthorsCallsAuthorRepository()
    {
        $arrayCollection = new ArrayCollection();

        $authorRepository = $this->getMockBuilder(AuthorRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorRepository->expects($this->once())
            ->method('findAllAuthorsJoinedToBooks')
            ->willReturn($arrayCollection);

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->exactly(3))
            ->method('getRepository')
            ->willReturn($authorRepository);

        $libraryManager = $this->getMockBuilder(LibraryManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['getAllAuthors'])
            ->getMock();

        $result = $libraryManager->getAllAuthors();

        $this->assertTrue(
            $result instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
    }

    public function testGetAllGenresCallsGenreRepository()
    {
        $arrayCollection = new ArrayCollection();

        $genreRepository = $this->getMockBuilder(GenreRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $genreRepository->expects($this->once())
            ->method('findAllGenresJoinedToBooks')
            ->willReturn($arrayCollection);

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->exactly(3))
            ->method('getRepository')
            ->willReturn($genreRepository);

        $libraryManager = $this->getMockBuilder(LibraryManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['getAllGenres' ])
            ->getMock();

        $result = $libraryManager->getAllGenres();

        $this->assertTrue(
            $result instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
    }

    public function testGetMaxPages()
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->expects($this->once())
            ->method('count')
            ->willReturn(23);

        $doctrine = $this->createMock(ManagerRegistry::class);

        $libraryManager = $this->getMockBuilder(LibraryManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['getMaxPages' ])
            ->getMock();

        $result = $libraryManager->getMaxPages($paginator, 7);

        $this->assertEquals(4, $result, 'Retrieved result matches expected.');
    }
}
