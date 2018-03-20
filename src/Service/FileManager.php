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
     * @param UploadedFile $file
     * @param string $path Directory to upload the file to.
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

    private function generateFilename(string $extension)
    {
        return md5(uniqid()) . '_' . (string)date('dmYHms') . '.' . $extension;
    }

    public function createFileFromPath(string $path)
    {
        return new File($path);
    }

    public function deleteFile(string $path)
    {
        unlink($path);
    }
}
