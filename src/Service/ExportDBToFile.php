<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExportDBToFile {
    private $data;
    private $fileName;
    private $downloadDirectory;

    public function __construct($downloadDirectory)
    {
        $this->downloadDirectory = $downloadDirectory;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function dataToCSV()
    {
        $file = fopen($this->downloadDirectory."/".$this->fileName, 'w');
        foreach($this->data as $item) {
            $text = $item->getKey().",".$item->getValue()."\n";
            fwrite($file, $text);
        }
        fclose($file);
    }

    public function dataToPHP()
    {
        $file = fopen($this->downloadDirectory."/".$this->fileName, 'w');
        fwrite($file, "<?php"."\n");
        fwrite($file, "return ["."\n");
        
        foreach($this->data as $item) {
            $text = "'".$item->getKey()."'"."=>"."'".$item->getValue()."'".",\n";
            fwrite($file, $text);
        }

        fwrite($file, "];"."\n");
        fwrite($file, "?>"."\n");

        fclose($file);
    }

    public function download()
    {
        $file = $this->downloadDirectory."/".$this->fileName;
        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Length', filesize($file));
        $response->headers->set('Cache-Control', 'private');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }
}