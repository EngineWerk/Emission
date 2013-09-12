<?php

namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Entity\FileBlob;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $Repository = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File');
        $Query = $Repository->createQueryBuilder('f')
                ->orderBy('f.id', 'DESC')
                ->getQuery();
        
        $Files = $Query->getResult();
        
        $File = new FileBlob();
        $Form = $this->createFormBuilder($File)
                ->add('fileBlob')
                ->add('save', 'submit')
                ->getForm();
        
        return array('Files' => $Files, 'Form' => $Form->createView());
    }
    
    /**
     * @Route("/uploadxhr", name="upload_xhr_file")
     */
    public function uploadXHRAction(Request $request)
    {
        // TODO
        /**
         * Przeliczanie hasza md5 przed uploadem całego pliku.
         */
        $rangeTotalSize = null;
        $rangeStart = null;
        $rangeEnd = null;
        
        // Figure out if file is chunked and ranges for received file chunk
        if ($request->headers->get('Content-Range')) {
            
            //TODO preg_march rule
            $range = str_replace('bytes ', '', $request->headers->get('Content-Range')); 
            $range = explode('/', $range);
            $rangeTotalSize = trim($range[1]);
           
            $range = explode('-', $range[0]);
           
            $rangeStart = $range[0];
            $rangeEnd = $range[1];
           
            $formRequest = $request->request->get('form');
            $formRequest['rangeStart'] = $rangeStart;
            $formRequest['rangeEnd'] = $rangeEnd;
           
            $request->request->set('form', $formRequest);
        }
        
        $FileBlob = new FileBlob();
       
        $Form = $this->createFormBuilder($FileBlob)
                ->add('fileBlob')
                ->add('rangeStart')
                ->add('rangeEnd')
                ->add('save', 'submit')
                ->getForm();
        
        $Form->handleRequest($request);
        
        if ($Form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $fileTotalSize = (null !== $rangeTotalSize) ? $rangeTotalSize : $Form->get('fileBlob')->getData()->getSize();
            if (null === $rangeEnd || null === $rangeStart) {
                $rangeStart = 0;
                $rangeEnd = $fileTotalSize;
            }            
            
            // Find out if we have this File already
            $File = $this->getDoctrine()
                    ->getRepository('EnginewerkEmissionBundle:File')
                    ->findOneBy(array(
                        'name' => $Form->get('fileBlob')->getData()->getClientOriginalName(),
                        'size' => $fileTotalSize));
            
            // No? Lets create one
            if (null === $File) {
                $File = new File();
      
                $File->setName($Form->get('fileBlob')->getData()->getClientOriginalName());
                $File->setExtensionName($Form->get('fileBlob')->getData()->guessExtension());
                $File->setType($Form->get('fileBlob')->getData()->getMimeType());
                $File->setSize($fileTotalSize);
                $File->setIsComplete(false);
                $File->setUploadedBy($this->getUser()->getUserName());
                
                $em->persist($File);
            }
            
            // Find out if we have this FileBlob
            $FileBlobInStorage = $this->getDoctrine()
                    ->getRepository('EnginewerkEmissionBundle:FileBlob')
                    ->findOneBy(array(
                        'fileId' => $File->getId(),
                        'rangeStart' => $rangeStart,
                        'rangeEnd' => $rangeEnd));
            
            // No ? Lets create one
            if (null === $FileBlobInStorage) {
                $FileBlob->setFile($File);  
                $em->persist($FileBlob);
                $em->flush();  
            }
            
            // Do we have whole file?
            // Lets make sum for all renageEnd and check against declared File size
            $query = $this->getDoctrine()->getManager()
                    ->createQuery('SELECT SUM(f.size) as totalSize FROM EnginewerkEmissionBundle:FileBlob f WHERE f.fileId = :fileId')
                    ->setParameter('fileId', $File->getId());

            $totalSize = $query->getSingleScalarResult();

            if($totalSize == $File->getSize()) {
                
                // Set isComplete property to true
                $File->setIsComplete(true);
                $em->persist($File);
                $em->flush(); 
                
                // Return whole data to access file
                $jsonData = json_encode(array(
                      array(
                          'id' => $File->getId(),
                          'file_id' => $File->getFileId(),
                          'name' => $File->getName(),
                          'type' => $File->getType(),
                          'size' => $File->getSize(),
                          'expiration_date' => $File->getExpirationDate()->format('Y-m-d H:i:s'),
                          'updated_at' => $File->getUpdatedAt()->format('Y-m-d H:i:s'),
                          'created_at' => $File->getCreatedAt()->format('Y-m-d H:i:s'),
                          'uploaded_by' => $File->getUploadedBy(),
                          'show_url' => $this->generateUrl('show_file', array('file' => $File->getFileId()), true),
                          'download_url' => $this->generateUrl('download_file', array('file' => $File->getFileId()), true),
                          'open_url' => $this->generateUrl('open_file', array('file' => $File->getFileId()), true),
                          'delete_url' => $this->generateUrl('delete_file', array('file' => $File->getFileId()), true)
                      ),
                ));
            } else {
                $jsonData = json_encode(array(
                      array(
                          'id' => $File->getId(),
                          'file_id' => $File->getFileId(),
                          'name' => $File->getName(),
                          'type' => $File->getType(),
                          'size' => $File->getSize()
                      ),
                ));
            }

            

        } else {
            
            $jsonData = json_encode(array(
                  array(
                      'errors' => var_export($Form->getErrors(), true)
                  ),
            ));

            
        }
        
        $headers = array(
                'Content-Type' => 'application/json'
          );

        $response = new Response($jsonData, 200, $headers);

        return $response;
    }

    /**
     * @Route("/upload", name="upload_file")
     * @Template("EnginewerkEmissionBundle:Default:upload.html.twig")
     */
    public function uploadAction(Request $request)
    {
        $FileBlob = new FileBlob();
       
        $Form = $this->createFormBuilder($FileBlob)
                ->add('fileBlob')
                ->add('save', 'submit')
                ->getForm();
        
        $Form->handleRequest($request);
        
        if ($Form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('name' => $Form->get('fileBlob')->getData()->getClientOriginalName()));
            var_dump($File);
            if(null === $File) {
            //if(!$FileBlob->getFile()) {
                
                $File = new File();
      
                $File->setName($Form->get('fileBlob')->getData()->getClientOriginalName());
                $File->setExtensionName($Form->get('fileBlob')->getData()->guessExtension());
                $File->setType($Form->get('fileBlob')->getData()->getMimeType());
                $File->setSize($Form->get('fileBlob')->getData()->getSize());
                
                $em->persist($File);   
            }
            
            //$FileBlob->setFile($File);
            $em->persist($FileBlob);
            $em->flush();  
            
            return $this->redirect($this->generateUrl('enginewerk_emission_default_index'));
        }

        return array('Form' => $Form->createView());
    }
    
    /**
     * @Route("/f/{file}", requirements={"file"}, name="show_file")
     * @Template()
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws type
     */
    public function showFileAction(Request $request)
    {
        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));
        
        if(!$File)
            throw $this->createNotFoundException('File not found');
        
        return array('File' => $File);
    }
    
    /**
     * @Route("/d/{file}", requirements={"file"}, name="download_file", defaults={"dl" = 1})
     * @Route("/o/{file}", requirements={"file"}, name="open_file")
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function downloadFileAction(Request $request)
    {
        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if(null === $File) {
            throw $this->createNotFoundException('File not found');
        }
        
        $FileBlobs = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlob')
                ->findBy(array('fileId' => $File->getId()), array('rangeStart' => 'ASC'));
        
        foreach ($FileBlobs as $FileBlob) {
            $filePath = $FileBlob->getAbsolutePath();

            if(!file_exists($filePath) || is_dir($filePath)) {
                throw $this->createNotFoundException('File doesn`t exists');
            }
        }

        // TODO Download set_time_limit
        set_time_limit(0);
        $response = new Response();
       
        $response->headers->set('Content-Type', $File->getType());
        $response->headers->set('Content-Length', $File->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if($request->get('dl')) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $File->getName().'"');
        }
        
        $response->sendHeaders();
        
        foreach ($FileBlobs as $FileBlob) {
            $filePath = $FileBlob->getAbsolutePath();
            readfile($filePath);
        }        
    }
    
    /**
     * @Route("/delete/{file}", requirements={"file"}, name="delete_file")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function deleteAction(Request $request)
    {
        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));
        
        if(!$File) {
            throw $this->createNotFoundException('File not found');
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($File);
        $em->flush();  
        
        return $this->redirect($this->generateUrl('enginewerk_emission_default_index'));
    }
}
