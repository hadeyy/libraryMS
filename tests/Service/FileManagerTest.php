<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/28/2018
 * Time: 4:40 PM
 */

namespace App\Tests\Service;


use App\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerTest extends WebTestCase
{
    public function testUpload()
    {
        $fileManager = new FileManager();

        $filename = 'test_upload_file.jpg';
        fopen($filename, 'w');

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$filename, $filename])
            ->getMock();
        $uploadedFile->expects($this->once())
            ->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile->expects($this->once())
            ->method('move')
            ->with(
                $this->isType('string'),
                $this->isType('string')
            );

        $result = $fileManager->upload($uploadedFile, 'some/path');

        $this->assertTrue(is_string($result), 'Result is a string.');
        $this->assertContains('_', $result, 'Result contains underscore.');
        $this->assertContains('.jpg', $result, 'Result contains file extension.');

        unlink($filename);
    }

    public function testCreateFileFromPath()
    {
        $filePath = 'test_create_file.jpg';
        fopen($filePath, 'w');

        $fileManager = new FileManager();

        $result = $fileManager->createFileFromPath($filePath);
        $this->assertTrue($result instanceof File, 'Result is an instance of File');
        $this->assertEquals($filePath, $result->getFilename(), 'Created file name matches expected.');

        unlink($filePath);
    }

    public function testDeleteFile()
    {
        $filePath = 'test_delete_file.jpg';
        fopen($filePath, 'w');

        $this->assertFileExists($filePath, 'File exists.');

        $fileManager = new FileManager();

        $fileManager->deleteFile($filePath);

        $this->assertFileNotExists($filePath, 'File has been deleted.');
    }
}
