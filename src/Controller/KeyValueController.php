<?php

namespace App\Controller;

use App\Service\FileUploader;
use App\Service\CSVFileToDB;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\KeyValue;

use App\Form\KeyValueType;
use App\Form\FileSelectionType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class KeyValueController extends AbstractController {
    /**
     * @Route("/keyvalue", name="keyvalue_main")
     */
    public function index(Request $request, FileUploader $fileUploader, CSVFileToDB $CSVFileToDB) {
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
            if ($selectedFile && $fileUploader->checkFileType($selectedFile, 'csv')) {
                $fileUploader->upload($selectedFile, 'csv');

                $CSVFileToDB->setTargetFile('updata.csv');
                $CSVFileToDB->resetTable('key_value', 'key_value_id_seq');
                $CSVFileToDB->arrayToDB($CSVFileToDB->csvToArray());
            }
            return $this->redirectToRoute('keyvalue_main');
        }

        return $this->render('key_value/index.html.twig', [
            'keyvalues' => $keyvalues,
            'KeyValueForm' => $addKeyValueForm->createView(),
            'FileUploadForm' => $FileSelectionForm->createView(),
        ]);
    }

    /**
     * @Route("/keyvalue/edit/{id}", name="keyvalue_edit")
     */
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

    /**
     * @Route("/keyvalue/delete/{id}", name="keyvalue_delete")
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $item = $entityManager->getRepository(KeyValue::class)->find($id);
        $entityManager->remove($item);
        $entityManager->flush();

        return $this->redirectToRoute('keyvalue_main');
    }

    /**
     * @Route("keyvalue/export/{type}", name="keyvalue_export")
     */
    public function export($type)
    {
        if($type === 'csv') {
            $test = 1;
        } elseif ($type === 'php') {
            $test = 2;
        }
        $filePath = $this->getParameter('kernel.project_dir')."/var/uploads/csv/updata.csv";

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }

}
