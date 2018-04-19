<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/1/2018
 * Time: 4:20 PM
 */

namespace App\Tests\Service;


use App\Entity\Author;
use App\Service\AuthorManager;
use App\Service\FileManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AuthorManagerTest extends WebTestCase
{
    public function testCreateFromArray()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Author::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);

        $authorManager = new AuthorManager($doctrine, $fileManager, 'path/to/directory');

        $data = [
            'firstName' => 'testFirstName',
            'lastName' => 'testLastName',
            'country' => 'testCountry',
            'portrait' => 'testPortrait',
        ];

        $authorManager->createFromArray($data);
    }

    public function testCreateArrayFromAuthorWithAllData()
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $file = $this->createMock(File::class);
        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('createFileFromPath')
            ->with($this->isType('string'))
            ->willReturn($file);

        $authorManager = new AuthorManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author(
            'firstName',
            'lastName',
            'country',
            'portrait'
        );

        $data = $authorManager->createArrayFromAuthor($author);

        $this->assertTrue(is_array($data), 'Result is an array.');
        $this->assertCount(
            4, $data,
            'Array contains the correct number of elements.'
        );
        $this->assertArrayHasKey('firstName', $data);
        $this->assertEquals($data['firstName'], $author->getFirstName());
        $this->assertArrayHasKey('lastName', $data);
        $this->assertEquals($data['lastName'], $author->getLastName());
        $this->assertArrayHasKey('country', $data);
        $this->assertEquals($data['country'], $author->getCountry());
        $this->assertArrayHasKey('portrait', $data);
        $this->assertEquals($data['portrait'], $file);
    }

    public function testCreateArrayFromAuthorWithRequiredData()
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('createFileFromPath');

        $authorManager = new AuthorManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author(
            'firstName',
            null,
            'country',
            null
        );

        $data = $authorManager->createArrayFromAuthor($author);

        $this->assertTrue(is_array($data), 'Result is an array.');
        $this->assertCount(
            4, $data,
            'Array contains the correct number of elements.'
        );
        $this->assertArrayHasKey('firstName', $data);
        $this->assertEquals($data['firstName'], $author->getFirstName());
        $this->assertArrayHasKey('lastName', $data);
        $this->assertEquals($data['lastName'], $author->getLastName());
        $this->assertArrayHasKey('country', $data);
        $this->assertEquals($data['country'], $author->getCountry());
        $this->assertArrayHasKey('portrait', $data);
        $this->assertEquals($data['portrait'], $author->getPortrait());
    }

    public function testUpdateAuthorWithNewPortrait()
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
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $authorManager = new AuthorManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author(
            'firstName',
            'lastName',
            'country',
            'portrait'
        );
        $filePath = 'test_update_portrait.jpg';
        fopen($filePath, 'w');
        $data = [
            'firstName' => 'newFirstName',
            'lastName' => 'newLastName',
            'country' => 'newCountry',
            'portrait' => new UploadedFile($filePath, $filePath),
        ];

        $authorManager->updateAuthor($author, $data);

        $this->assertEquals(
            'newFirstName', $author->getFirstName(),
            'First name has been updated.'
        );
        $this->assertEquals(
            'newLastName', $author->getLastName(),
            'Last name has been updated.'
        );
        $this->assertEquals(
            'newCountry', $author->getCountry(),
            'Country has been updated.'
        );
        $this->assertEquals(
            'filename', $author->getPortrait(),
            'Author portrait has been updated.'
        );

        unlink('test_update_portrait.jpg');
    }

    public function testUpdateAuthorWithoutNewPortrait()
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

        $authorManager = new AuthorManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author(
            'firstName',
            'lastName',
            'country',
            'portrait'
        );

        $data = [
            'firstName' => 'testFirstName',
            'lastName' => 'testLastName',
            'country' => 'testCountry',
            'portrait' => null,
        ];

        $authorManager->updateAuthor($author, $data);

        $this->assertEquals(
            'portrait', $author->getPortrait(),
            'Original portrait was not changed.'
        );
    }

    public function testRemoveAuthorWithPortrait()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Author::class));
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

        $authorManager = new AuthorManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author(
            'firstName',
            null,
            'country',
            'portrait'
        );

        $authorManager->remove($author);
    }

    public function testRemoveAuthorWithoutPortrait()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Author::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('deleteFile');

        $authorManager = new AuthorManager($doctrine, $fileManager, 'path/to/directory');

        $author = new Author('firstName', null, 'country');

        $authorManager->remove($author);
    }
}
