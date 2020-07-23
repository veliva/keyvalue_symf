<?php

namespace App\Controller;

use App\Service\FileUploader;
use App\Service\CSVFileToDB;
use App\Service\ExportDBToFile;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\KeyValue;

use App\Form\KeyValueType;
use App\Form\FileSelectionType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class KeyValueController extends AbstractController {

    private $fileUploader;
    private $CSVFileToDB;
    private $ExportDBToFile;

    public function __construct(FileUploader $fileUploader, CSVFileToDB $CSVFileToDB, ExportDBToFile $ExportDBToFile) {
        $this->fileUploader = $fileUploader;
        $this->CSVFileToDB = $CSVFileToDB;
        $this->ExportDBToFile = $ExportDBToFile;
    }
    
    public function index(Request $request) {
        $keyvalues = $this->getDoctrine()
        ->getRepository(KeyValue::class)
        ->findAll();

        $keyvalue = new KeyValue();

        $addKeyValueForm = $this->createForm(KeyValueType::class, $keyvalue, ['save_button_label' => 'Add']);
        $addKeyValueForm->handleRequest($request);

        if($addKeyValueForm->isSubmitted() && $addKeyValueForm->isValid()) {
            $inputs = $addKeyValueForm->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($inputs);
            $entityManager->flush();

            return $this->redirectToRoute('keyvalue_main');
        }

        $FileSelectionForm = $this->createForm(FileSelectionType::class);
        $FileSelectionForm->handleRequest($request);

        if ($FileSelectionForm->isSubmitted() && $FileSelectionForm->isValid()) {
            $selectedFile = $FileSelectionForm['attachment']->getData();
            if ($selectedFile && $this->fileUploader->checkFileType($selectedFile, 'csv')) {
                $this->fileUploader->upload($selectedFile, 'csv');

                $this->CSVFileToDB->setTargetFile('updata.csv');
                $this->CSVFileToDB->resetTable('key_value', 'key_value_id_seq');
                $this->CSVFileToDB->arrayToDB($this->CSVFileToDB->csvToArray());
            }
            return $this->redirectToRoute('keyvalue_main');
        }

        return $this->render('key_value/index.html.twig', [
            'keyvalues' => $keyvalues,
            'KeyValueForm' => $addKeyValueForm->createView(),
            'FileUploadForm' => $FileSelectionForm->createView(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $keyvalue = new KeyValue;
        $keyvalue = $this->getDoctrine()->getRepository(KeyValue::class)->find($id);

        $form = $this->createForm(KeyValueType::class, $keyvalue, ['save_button_label' => 'Save']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('keyvalue_main');
        }

        return $this->render('key_value/edit.html.twig', [
            'KeyValueForm' => $form->createView(),
        ]);
    }

    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $item = $entityManager->getRepository(KeyValue::class)->find($id);
        $entityManager->remove($item);
        $entityManager->flush();

        return $this->redirectToRoute('keyvalue_main');
    }

    public function export($type)
    {
        $keyvalues = $this->getDoctrine()
        ->getRepository(KeyValue::class)
        ->findAll();

        $this->ExportDBToFile->setData($keyvalues);
        
        if($type === 'csv') {
            $this->ExportDBToFile->setFileName('data.csv');
            $this->ExportDBToFile->dataToCSV();
        } elseif ($type === 'php') {
            $this->ExportDBToFile->setFileName('data.php');
            
        } else {
            return;
        }

        $response = $this->ExportDBToFile->download();

        return $response;
    }

}
