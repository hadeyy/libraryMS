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
    public function testCreateAddsDataToBook()
    {
        $bookManager = $this->getMockBuilder(BookManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $author = $this->createMock(Author::class);
        $date = $this->createMock(\DateTime::class);
        $data = [
            'ISBN' => 'ISBN',
            'title' => 'title',
            'author' => $author,
            'pages' => 123,
            'language' => 'language',
            'publisher' => 'publisher',
            'publicationDate' => $date,
            'availableCopies' => 1,
            'cover' => 'cover',
            'annotation' => 'annotation'
        ];

        $book = $bookManager->create($data);

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
        $this->assertEquals('cover', $book->getCover());
        $this->assertEquals('annotation', $book->getAnnotation());

        return $book;
    }

    /**
     * @depends testCreateAddsDataToBook
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

        $userManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept(['createArrayFromBook'])
            ->getMock();

        $data = $userManager->createArrayFromBook($book);
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

    /**
     * @depends testCreateAddsDataToBook
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

        $bookManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['remove', 'saveChanges'])
            ->getMock();

        $bookManager->remove($book);
    }

    public function testSavingMethodsCallEntityManager()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Book::class));
        $entityManager->expects($this->exactly(2))
            ->method('flush');

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('upload')
            ->with(
                $this->isInstanceOf(UploadedFile::class),
                $this->isType('string')
            )
            ->willReturn('filename');

        $bookManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['save', 'saveChanges', 'getCover', 'setCover'])
            ->getMock();

        $author = $this->createMock(Author::class);
        $cover = $this->createMock(UploadedFile::class);
        $book = new Book(
            'ISBN',
            'title',
            $author,
            111,
            'language',
            'publisher',
            new \DateTime(),
            1,
            $cover,
            'annotation'
        );

        $bookManager->save($book);
        $bookManager->saveChanges();
    }

    public function testUpdateBookChangesBookDataWithNewCover()
    {
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

        $filePath = 'test_update_cover.jpg';
        fopen($filePath, 'w');
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
            'cover' => new UploadedFile($filePath, $filePath),
            'genres' => [$genre],
        ];

        $doctrine = $this->createMock(ManagerRegistry::class);

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

        $bookManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['updateBook'])
            ->getMock();
        $bookManager->expects($this->once())
            ->method('saveChanges');

        $bookManager->updateBook($book, $newData);

        $this->assertNotEquals(
            'ISBN', $book->getISBN(),
            'Updated ISBN is not equal to original.'
        );
        $this->assertNotEquals(
            'title', $book->getTitle(),
            'Updated title is not equal to original.'
        );
        $this->assertNotEquals(
            $author, $book->getAuthor(),
            'Updated Author is not equal to original.'
        );
        $this->assertNotEquals(
            111, $book->getPages(),
            'Updated page number is not equal to original.'
        );
        $this->assertNotEquals(
            'language', $book->getLanguage(),
            'Updated language is not equal to original.'
        );
        $this->assertNotEquals(
            'publisher', $book->getPublisher(),
            'Updated publisher is not equal to original.'
        );
        $this->assertNotEquals(
            $date, $book->getPublicationDate(),
            'Updated publication date is not equal to original.'
        );
        $this->assertNotEquals(
            1, $book->getAvailableCopies(),
            'Updated available copy number is not equal to original.'
        );
        $this->assertNotEquals(
            'annotation', $book->getAnnotation(),
            'Updated annotation is not equal to original.'
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

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('deleteFile');
        $fileManager->expects($this->exactly(0))
            ->method('upload');

        $bookManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['updateBook'])
            ->getMock();
        $bookManager->expects($this->once())
            ->method('saveChanges');

        $bookManager->updateBook($book, $newData);

        $this->assertEquals(
            'cover', $book->getCover(),
            'Original cover was not changed.'
        );
    }
}
