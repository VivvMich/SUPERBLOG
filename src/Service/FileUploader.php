<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload($file){


        if ( $file instanceof UploadedFile){

            $fileName = uniqid() . '.' . $file->guessExtension();

            try {
                $file->move($this->targetDirectory, $fileName);
            } catch (FileException $e){
                echo $e->getMessage();
            }
        }
        else{

            $fileName = $file;
        }


        return $fileName;

    }

}