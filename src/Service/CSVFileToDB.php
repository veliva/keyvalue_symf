<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\KeyValue;

class CSVFileToDB {
    private $targetDirectory;
    private $targetFile;
    private $entityManager;

    public function __construct($targetDirectory, EntityManagerInterface $entityManager)
    {
        $this->targetDirectory = $targetDirectory;
        $this->entityManager = $entityManager;
    }

    public function setTargetFile($tf) {
        $this->targetFile = $tf;
    }

    public function resetTable($tableName, $sequenceName) {
        $connection = $this->entityManager->getConnection();
        $connection->executeQuery('TRUNCATE '.$tableName.' RESTART IDENTITY;');
        $connection->executeQuery('ALTER SEQUENCE '.$sequenceName.' RESTART WITH 1;');
    }

    public function csvToArray() {
        $tempArray = array();
        $file = fopen($this->targetDirectory.'/'.$this->targetFile, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            array_push($tempArray, array($line[0], $line[1]));
        }
        fclose($file);
        
        return $tempArray;
    }

    public function arrayToDB ($array) {
        foreach($array as $row){
            $entityKeyvalue = new KeyValue();

            $entityKeyvalue->setKey($row[0]);
            $entityKeyvalue->setValue($row[1]);
            $this->entityManager->persist($entityKeyvalue);
        }
        $this->entityManager->flush();
    }

}