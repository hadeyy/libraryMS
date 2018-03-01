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

/** @TODO FIXME */
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
        $extension = $this->guessExtension($file);
        $filename = $this->generateFilename($extension);

        $this->move($file, $path, $filename);

        return $filename;
    }

    public function guessExtension(File $file)
    {
        return $file->guessExtension();
    }

    public function generateFilename(string $extension)
    {
        return md5(uniqid()) . '_' . (string)date('dmYHms') . '.' . $extension;
    }

    public function move(File $file, string $path, string $filename)
    {
        $file->move($path, $filename);
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
