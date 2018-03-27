<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 1:33 PM
 */

namespace App\Service;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    /**
     * Generates a name for the uploaded file and moves the file to the given directory.
     *
     * @param UploadedFile $file
     * @param string $path Path to directory to upload the file to.
     *
     * @return string Uploaded file name and extension.
     */
    public function upload(UploadedFile $file, string $path)
    {
        $extension = $file->guessExtension();
        $filename = $this->generateFilename($extension);

        $file->move($path, $filename);

        return $filename;
    }

    /**
     * Generates a random filename containing current datetime and the file's extension.
     *
     * @param string $extension Filename extension.
     *
     * @return string
     */
    private function generateFilename(string $extension)
    {
        return md5(uniqid()) . '_' . (string)date('dmYHms') . '.' . $extension;
    }

    /**
     * Creates an instance of File from the path to the file.
     *
     * @param string $path Path to the file.
     *
     * @return File
     */
    public function createFileFromPath(string $path)
    {
        return new File($path);
    }

    /**
     * Deletes a file from it's directory.
     *
     * @param string $path Path to the file.
     *
     * @return void
     */
    public function deleteFile(string $path)
    {
        unlink($path);
    }
}
