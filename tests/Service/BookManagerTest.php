<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/1/2018
 * Time: 1:00 PM
 */

namespace App\Tests\Service;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use App\Service\BookManager;
use App\Service\FileManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookManagerTest extends WebTestCase
{
    public function testCreateBookFromArray()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Book::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);

        $bookManager = new BookManager($doctrine, $fileManager, 'path/to/directory');

        $author = $this->createMock(Author::class);
        $date = $this->createMock(\DateTime::class);
        $cover = $this->createMock(UploadedFile::class);
        $data = [
            'ISBN' => 'ISBN',
            'title' => 'title',
            'author' => $author,
            'pages' => 123,
            'language' => 'language',
            'publisher' => 'publisher',
            'publicationDate' => $date,
            'availableCopies' => 1,
            'cover' => $cover,
            'annotation' => 'annotation'
        ];

        $book = $bookManager->createFromArray($data);

        $this->assertTrue(
            $book instanceof Book,
            'Result is an instance of Book class.'
        );
        $this->assertEquals('ISBN', $book->getISBN());
        $this->assertEquals('title', $book->getTitle());
        $this->assertEquals($author, $book->getAuthor());
        $this->assertEquals(123, $book->getPages());
        $this->assertEquals('language', $book->getLanguage());
        $this->assertEquals('publisher', $book->getPublisher());
        $this->assertEquals($date, $book->getPublicationDate());
        $this->assertEquals(1, $book->getAvailableCopies());
        $this->assertEquals('annotation', $book->getAnnotation());

        return $book;
    }

    /**
     * @depends testCreateBookFromArray
     * @param Book $book
     */
    public function testCreateArrayFromBook(Book $book)
    {
        $file = $this->createMock(File::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('createFileFromPath')
            ->with($this->isType('string'))
            ->willReturn($file);

        $doctrine = $this->createMock(ManagerRegistry::class);

        $bookManager = new BookManager($doctrine, $fileManager, 'path/to/directory');

        $data = $bookManager->createArrayFromBook($book);

        $this->assertTrue(is_array($data), 'Result is an array.');
        $this->assertCount(
            11, $data,
            'Array contains the correct number of elements.'
        );
        $this->assertArrayHasKey('ISBN', $data);
        $this->assertEquals($data['ISBN'], $book->getISBN());
        $this->assertArrayHasKey('title', $data);
        $this->assertEquals($data['title'], $book->getTitle());
        $this->assertArrayHasKey('author', $data);
        $this->assertEquals($data['author'], $book->getAuthor());
        $this->assertArrayHasKey('pages', $data);
        $this->assertEquals($data['pages'], $book->getPages());
        $this->assertArrayHasKey('language', $data);
        $this->assertEquals($data['language'], $book->getLanguage());
        $this->assertArrayHasKey('publisher', $data);
        $this->assertEquals($data['publisher'], $book->getPublisher());
        $this->assertArrayHasKey('publicationDate', $data);
        $this->assertEquals($data['publicationDate'], $book->getPublicationDate());
        $this->assertArrayHasKey('availableCopies', $data);
        $this->assertEquals($data['availableCopies'], $book->getAvailableCopies());
        $this->assertArrayHasKey('cover', $data);
        $this->assertEquals($data['cover'], $file);
        $this->assertArrayHasKey('annotation', $data);
        $this->assertEquals($data['annotation'], $book->getAnnotation());
        $this->assertArrayHasKey('genres', $data);
        $this->assertEquals($data['genres'], $book->getGenres());
    }

    public function testUpdateBookWithNewCover()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('deleteFile')
            ->with($this->isType('string'));
        $fileManager->expects($this->once())
            ->method('upload')
            ->with(
                $this->isInstanceOf(UploadedFile::class),
                $this->isType('string')
            )
            ->willReturn('filename');

        $bookManager = new BookManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author('firstName', 'lastName', 'country');
        $date = new \DateTime();
        $book = new Book(
            'ISBN',
            'title',
            $author,
            111,
            'language',
            'publisher',
            $date,
            1,
            'cover',
            'annotation'
        );

        $newAuthor = new Author('Test', 'Testerson', 'Testland');
        $newDate = new \DateTime();
        $filePath = 'test_update_cover.jpg';
        fopen($filePath, 'w');
        $genre = new Genre('genre');
        $newData = [
            'ISBN' => 'newISBN',
            'title' => 'newTitle',
            'author' => $newAuthor,
            'pages' => 739,
            'language' => 'newLanguage',
            'publisher' => 'newPublisher',
            'publicationDate' => $newDate,
            'availableCopies' => 22,
            'annotation' => 'newAnnotation',
            'cover' => new UploadedFile($filePath, $filePath),
            'genres' => [$genre],
        ];

        $bookManager->updateBook($book, $newData);

        $this->assertEquals(
            'newISBN', $book->getISBN(),
            'ISBN has been updated.'
        );
        $this->assertEquals(
            'newTitle', $book->getTitle(),
            'Title has been updated.'
        );
        $this->assertEquals(
            $newAuthor, $book->getAuthor(),
            'Author has been updated.'
        );
        $this->assertEquals(
            739, $book->getPages(),
            'Page number has been updated.'
        );
        $this->assertEquals(
            'newLanguage', $book->getLanguage(),
            'Language has been updated.'
        );
        $this->assertEquals(
            'newPublisher', $book->getPublisher(),
            'Publisher has been updated.'
        );
        $this->assertEquals(
            $newDate, $book->getPublicationDate(),
            'Publication date has been updated.'
        );
        $this->assertEquals(
            22, $book->getAvailableCopies(),
            'Available copy number has been updated.'
        );
        $this->assertEquals(
            'newAnnotation', $book->getAnnotation(),
            'Annotation has been updated.'
        );
        $this->assertEquals(
            'filename', $book->getCover(),
            'Book cover has been updated.'
        );
        $this->assertEquals(
            new ArrayCollection([$genre]), $book->getGenres(),
            'Book genres have been updated.'
        );

        unlink('test_update_cover.jpg');
    }

    public function testUpdateBookWithoutNewCover()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('deleteFile');
        $fileManager->expects($this->exactly(0))
            ->method('upload');

        $bookManager = new BookManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author('firstName', 'lastName', 'country');
        $date = new \DateTime();
        $book = new Book(
            'ISBN',
            'title',
            $author,
            111,
            'language',
            'publisher',
            $date,
            1,
            'cover',
            'annotation'
        );
        $genre = new Genre('genre');
        $newData = [
            'ISBN' => 'testISBN',
            'title' => 'testTitle',
            'author' => new Author('Test', 'Testerson', 'Testeria'),
            'pages' => 739,
            'language' => 'testLanguage',
            'publisher' => 'testPublisher',
            'publicationDate' => new \DateTime(),
            'availableCopies' => 22,
            'annotation' => 'testAnnotation',
            'cover' => null,
            'genres' => [$genre],
        ];

        $bookManager->updateBook($book, $newData);

        $this->assertEquals(
            'cover', $book->getCover(),
            'Original cover was not changed.'
        );
    }

    /**
     * @depends testCreateBookFromArray
     * @param Book $book
     */
    public function testRemoveCallsFileAndEntityManagers(Book $book)
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Book::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('deleteFile')
            ->with($this->isType('string'));

        $bookManager = new BookManager($doctrine, $fileManager, 'path/to/directory');

        $bookManager->remove($book);
    }
}
