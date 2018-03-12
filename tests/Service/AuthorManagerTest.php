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
    public function testCreateAddsDataToAuthor()
    {
        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $data = [
            'firstName' => 'testFirstName',
            'lastName' => 'testLastName',
            'country' => 'testCountry',
            'portrait' => 'testPortrait',
        ];

        $author = $authorManager->create($data);

        $this->assertTrue(
            $author instanceof Author,
            'Result is an instance of Author class.'
        );
        $this->assertEquals('testFirstName', $author->getFirstName());
        $this->assertEquals('testCountry', $author->getCountry());
        $this->assertEquals('testLastName', $author->getLastName());
        $this->assertEquals('testPortrait', $author->getPortrait());
    }

    public function testCreateArrayFromAuthorWithAllData()
    {
        $author = new Author(
            'firstName',
            'lastName',
            'country',
            'portrait'
        );

        $file = $this->createMock(File::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('createFileFromPath')
            ->with($this->isType('string'))
            ->willReturn($file);

        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept(['createArrayFromAuthor'])
            ->getMock();

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
        $author = new Author(
            'firstName',
            null,
            'country',
            null
        );

        $doctrine = $this->createMock(ManagerRegistry::class);
        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('createFileFromPath');

        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept(['createArrayFromAuthor'])
            ->getMock();

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

    public function testUpdateAuthorChangesAuthorDataWithNewPortrait()
    {
        $author = new Author(
            'firstName',
            'lastName',
            'country',
            'portrait'
        );

        $filePath = 'test_update_portrait.jpg';
        fopen($filePath, 'w');
        $newData = [
            'firstName' => 'testFirstName',
            'lastName' => 'testLastName',
            'country' => 'testCountry',
            'portrait' => new UploadedFile($filePath, $filePath),
        ];

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('deleteFile')
            ->with($this->isType('string'));
        $fileManager->expects($this->once())
            ->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept(['updateAuthor'])
            ->getMock();
        $authorManager->expects($this->once())
            ->method('saveChanges');

        $authorManager->updateAuthor($author, $newData);

        $this->assertNotEquals(
            'firstName', $author->getFirstName(),
            'Updated first name is not equal to original.'
        );
        $this->assertNotEquals(
            'lastName', $author->getLastName(),
            'Updated last name is not equal to original.'
        );
        $this->assertNotEquals(
            'country', $author->getCountry(),
            'Updated country is not equal to original.'
        );
        $this->assertEquals(
            'filename', $author->getPortrait(),
            'Author portrait has been updated.'
        );

        unlink('test_update_portrait.jpg');
    }

    public function testUpdateAuthorWithoutNewPortrait()
    {
        $author = new Author(
            'firstName',
            'lastName',
            'country',
            'portrait'
        );

        $newData = [
            'firstName' => 'testFirstName',
            'lastName' => 'testLastName',
            'country' => 'testCountry',
            'portrait' => null,
        ];

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('deleteFile');
        $fileManager->expects($this->exactly(0))
            ->method('upload');

        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept(['updateAuthor'])
            ->getMock();
        $authorManager->expects($this->once())
            ->method('saveChanges');

        $authorManager->updateAuthor($author, $newData);

        $this->assertEquals(
            'portrait', $author->getPortrait(),
            'Original portrait was not changed.'
        );
    }

    public function testSavingMethodsCallEntityManager()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Author::class));
        $entityManager->expects($this->exactly(2))
            ->method('flush');
        $entityManager->expects($this->exactly(2))
            ->method('clear');

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);

        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['save', 'saveChanges'])
            ->getMock();

        $authorManager->save(new Author('firstName', null, 'country'));
        $authorManager->saveChanges();
    }

    public function testRemoveAuthorWithPortraitCallsFileAndEntityManagers()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Author::class));
        $entityManager->expects($this->once())
            ->method('flush');
        $entityManager->expects($this->once())
            ->method('clear');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('deleteFile')
            ->with($this->isType('string'));

        $bookManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['remove', 'saveChanges'])
            ->getMock();

        $bookManager->remove(
            new Author(
                'firstName',
                null,
                'country',
                'portrait'
            )
        );
    }

    public function testRemoveAuthorWithoutPortraitCallsOnlyEntityManager()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Author::class));
        $entityManager->expects($this->once())
            ->method('flush');
        $entityManager->expects($this->once())
            ->method('clear');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('deleteFile');

        $bookManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['remove', 'saveChanges'])
            ->getMock();

        $bookManager->remove(new Author('firstName', null, 'country'));
    }
}
