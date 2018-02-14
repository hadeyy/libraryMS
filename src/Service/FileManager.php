<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 1:33 PM
 */

namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    /**
     * @param UploadedFile $file
     * @param string $path Directory to upload the file to.
     *
     * @return string Uploaded file name.
     */
    public function upload(UploadedFile $file, string $path)
    {
        $extension = $file->guessExtension();
        $filename = md5(uniqid()) . '_' . (string)date('dmYHms') . '.' . $extension;

        $file->move($path, $filename);

        return $filename;
    }
}
