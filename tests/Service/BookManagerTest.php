<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/1/2018
 * Time: 1:00 PM
 */

namespace App\Tests\Service;


use App\Entity\Book;
use App\Service\BookManager;
use App\Service\FileManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BookManagerTest extends WebTestCase
{
    public function testCreate()
    {
        $bookManager = $this->getMockBuilder(BookManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $book = $bookManager->create();

        $this->assertTrue($book instanceof Book, 'Result is an instance of Book class');
    }

    public function testSavingMethodsCallEntityManagerMethods()
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
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $bookManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['save', 'saveChanges', 'getCover', 'setCover'])
            ->getMock();

        $uploadedFile = $this->createMock(UploadedFile::class);
        $book = new Book();
        $book->setCover($uploadedFile);

        $bookManager->save($book);
        $bookManager->saveChanges();
    }

    public function testChangePhotoFromPathToFileCreatesAFile()
    {
        $book = new Book();
        $book->setCover('test.jpg');

        $filePath = 'test_file.jpg';
        fopen($filePath, 'w');

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('createFileFromPath')
            ->willReturn(new File($filePath));

        $doctrine = $this->createMock(ManagerRegistry::class);

        $bookManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept([
                'changePhotoFromPathToFile',
                'setPhotoName',
                'getPhotoName',
                'setPhotoPath',
                'getPhotoPath',
                'setCover',
                'getCover',
            ])
            ->getMock();

        $this->assertTrue(is_string($book->getCover()), "Book cover is stored as string.");
        $bookManager->changePhotoFromPathToFile($book);
        $this->assertTrue(
            $book->getCover() instanceof File,
            "Book cover successfully changed from string to an instance of File."
        );

        unlink($filePath);
    }


    /**
     * @dataProvider bookCoverProvider
     * @param $cover
     * @param bool $isString
     */
    public function testUpdateBookChangesBookCover($cover, bool $isString)
    {
        $book = new Book();
        $book->setCover($cover);
        $originalCover = $book->getCover();

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->any())
            ->method('deleteFile')
            ->with($this->isType('string'));
        $fileManager->expects($this->any())
            ->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $bookManager = $this->getMockBuilder(BookManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept([
                'updateBook',
                'setPhotoName',
                'getPhotoName',
                'setPhotoPath',
                'getPhotoPath',
                'setCover',
                'getCover',
            ])
            ->getMock();
        $bookManager->expects($this->once())
            ->method('saveChanges');

        $bookManager->setPhotoPath('path/to/photo');
        $bookManager->setPhotoName('filename');

        $bookManager->updateBook($book);

        $this->assertEquals('filename', $book->getCover(), "Book cover has been updated.");
        $this->assertNotEquals(
            $originalCover, $book->getCover(),
            'Updated cover is not equal to original.'
        );

        $isString ?: unlink('test_cover.jpg');
    }

    public function bookCoverProvider()
    {
        $filePath = 'test_cover.jpg';
        fopen($filePath, 'w');

        return [
            [new UploadedFile($filePath, $filePath), false],
            [$filePath, true]
        ];
    }

    public function testRemoveCallsFileAndEntityManagerMethods()
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
            ->setMethodsExcept(['remove'])
            ->getMock();
        $bookManager->expects($this->once())
            ->method('getCover')
            ->with($this->isInstanceOf(Book::class))
            ->willReturn('filename');

        $bookManager->remove(new Book());
    }
}
