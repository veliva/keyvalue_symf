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

    public function checkFileType(UploadedFile $file, $requiredFileType) {
        $fileType = $file->getClientOriginalExtension();
        if($fileType == $requiredFileType){
            return true;
        } else {
            return false;
        }
    }

    public function upload(UploadedFile $file, $requiredFileType)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = 'updata.'.$requiredFileType;

        try {
            $file->move($this->getTargetDirectory(), $fileName);
            echo "File uploaded!";
        } catch (FileException $e) {
            echo "File was not uploaded, ".$e;
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
