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
    public function testCreate()
    {
        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $author = $authorManager->create();

        $this->assertTrue($author instanceof Author, 'Result is an instance of Author class.');
    }

    /**
     * @dataProvider portraitProvider
     * @param $portrait
     * @param $newFile
     */
    public function testUpdateAuthorChangesPortrait($portrait, $newFile)
    {
        $author = new Author();
        $author->setPortrait($portrait);

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->any())
            ->method('deleteFile')
            ->with($this->isType('string'));
        $fileManager->expects($this->any())
            ->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), $this->isType('string'))
            ->willReturn('filename');

        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept([
                'updateAuthor',
                'getPortrait',
                'getPhotoPath',
            ])
            ->getMock();
        $authorManager->expects($this->any())
            ->method('getPhotoName');
        $authorManager->expects($this->once())
            ->method('setPortrait')
            ->with($this->isInstanceOf(Author::class), $this->anything());
        $authorManager->expects($this->once())
            ->method('saveChanges');

        $authorManager->updateAuthor($author);

        $newFile ? unlink('test_update_portrait.jpg') : null;
    }

    public function portraitProvider()
    {
        $filePath = 'test_update_portrait.jpg';
        fopen($filePath, 'w');
        $uploadedFile = $this->createMock(UploadedFile::class);

        return [
          [null, false],
          [$uploadedFile, true],
          ['test_portrait.jpg', false],
        ];
    }

    public function testUpdateAuthorWithoutPortrait()
    {
        $author = new Author();

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->exactly(0))
            ->method('deleteFile');
        $fileManager->expects($this->exactly(0))
            ->method('upload');

        $authorManager = $this->getMockBuilder(AuthorManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept([
                'updateAuthor',
                'getPortrait',
                'getPhotoName',
            ])
            ->getMock();
        $authorManager->expects($this->exactly(0))
            ->method('getPhotoPath');
        $authorManager->expects($this->once())
            ->method('setPortrait')
            ->with($this->isInstanceOf(Author::class), $this->anything());
        $authorManager->expects($this->once())
            ->method('saveChanges');

        $authorManager->setPhotoName($author->getPortrait());

        $authorManager->updateAuthor($author);

        $this->assertEquals(null, $author->getPortrait());
    }

    public function testChangePhotoFromPathToFileCreatesAFile()
    {
        $author = new Author();
        $author->setPortrait('test.jpg');

        $filePath = 'test_change_portrait.jpg';
        fopen($filePath, 'w');

        $doctrine = $this->createMock(ManagerRegistry::class);

        $fileManager = $this->createMock(FileManager::class);
        $fileManager->expects($this->once())
            ->method('createFileFromPath')
            ->with($this->isType('string'))
            ->willReturn(new File($filePath));

        $authorManger= $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, 'path/to/directory'])
            ->setMethodsExcept([
                'changePhotoFromPathToFile',
                'getPortrait',
                'setPhotoName',
                'setPhotoPath',
                'getPhotoName',
                'setPortrait',
                'getPhotoPath',
            ])
            ->getMock();

        $this->assertTrue(
            is_string($author->getPortrait()),
            'Author portrait is stored as string.'
        );
        $authorManger->changePhotoFromPathToFile($author);
        $this->assertTrue(
            $author->getPortrait() instanceof File,
            'Author portrait successfully changed from string to an instance of File.'
        );

        unlink($filePath);
    }

    public function testSavingMethodsCallEntityManagerMethods()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Author::class));
        $entityManager->expects($this->exactly(2))
            ->method('flush');

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

        $authorManager->save(new Author());
        $authorManager->saveChanges();
    }

    public function testRemoveCallsFileAndEntityManagerMethods()
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

        $bookManager = $this->getMockBuilder(AuthorManager::class)
            ->setConstructorArgs([$doctrine, $fileManager, ''])
            ->setMethodsExcept(['remove', 'saveChanges'])
            ->getMock();
        $bookManager->expects($this->once())
            ->method('getPortrait')
            ->with($this->isInstanceOf(Author::class))
            ->willReturn('filename');

        $bookManager->remove(new Author());
    }
}
