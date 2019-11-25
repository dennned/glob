<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploader
 * @package App\Service
 */
class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws \Exception
     */
    public function upload(UploadedFile $file): string
    {
        $date = new \DateTime();
        $newFilename = $date->format('Y-m-d') . '-' . md5(uniqid()) . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->getTargetDirectory(),
                $newFilename
            );
        } catch (FileException $e) {
            die($e->getMessage());
        }

        return $newFilename;
    }

    /**
     * @param string $imageName
     */
    public function delete(string $imageName = '')
    {
        $image = $this->getTargetDirectory() . '/' . $imageName;

        if (file_exists($image)) {
            unlink($image);
        }
    }

    /**
     * @return mixed
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
