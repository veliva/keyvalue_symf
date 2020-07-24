<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
// use Symfony\Component\HttpFoundation\File\Stream;

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

    //to csv
    public function dataToCSV()
    {
        $file = fopen($this->downloadDirectory."/".$this->fileName, 'w');
        foreach($this->data as $item) {
            $text = $item->getKey().",".$item->getValue()."\n";
            echo $text;
            fwrite($file, $text);
        }
        fclose($file);
    }

    //to php

    //download
    public function download()
    {
        $file = $this->downloadDirectory."/".$this->fileName;
        // $stream  = new Stream($this->downloadDirectory."/".$this->fileName);
        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Length', filesize($file));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }
}