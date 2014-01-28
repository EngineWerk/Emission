<?php

namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Enginewerk\EmissionBundle\Entity\FileBlob;
use Enginewerk\EmissionBundle\Response\AppResponse;

/**
 * DefaultController
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
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

        $FileBlob = new FileBlob();
        $Form = $this->createFormBuilder($FileBlob)
                ->add('fileBlob')
                ->add('save', 'submit')
                ->getForm();

        return array('Files' => $Files, 'Form' => $Form->createView());
    }

    /**
     * @Route("/f/{file}", requirements={"file"}, name="show_file")
     * @Template()
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @throws type
     */
    public function showFileAction(Request $request)
    {
        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (!$File) {
            throw $this->createNotFoundException('File not found');
        }

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

        if (null === $File) {
            throw $this->createNotFoundException('File not found');
        }

        $FileBlobs = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlob')
                ->findBy(array('fileId' => $File->getId()), array('rangeStart' => 'ASC'));

        foreach ($FileBlobs as $FileBlob) {
            $filePath = $FileBlob->getAbsolutePath();

            if (!file_exists($filePath) || is_dir($filePath)) {
                throw $this->createNotFoundException('File doesn`t exists');
            }
        }

        // TODO Download set_time_limit
        set_time_limit(0);
        $response = new StreamedResponse();
        
        $response->headers->set('Content-Type', $File->getType());
        $response->headers->set('Content-Length', $File->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($request->get('dl')) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $File->getName().'"');
        }

        $response->setCallback(function() use ($FileBlobs) {
            foreach($FileBlobs as $FileBlob) {
                $filePath = $FileBlob->getAbsolutePath();
                readfile($filePath);
            }
        });
        
        return $response;
    }

    /**
     * @Route("/delete/{file}", requirements={"file"}, name="delete_file")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function deleteAction(Request $request)
    {
        $appResponse = new AppResponse();

        $File = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File')->findOneBy(array('fileId' => $request->get('file')));

        if (!$File) {
            $appResponse->error('File not found');
        } else {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($File);
                $em->flush();

                $appResponse->success();

            } catch (Exception $e) {
                $appResponse->error('Can`t remove File');
            }
        }

        return new JsonResponse($appResponse->response(), 200);
    }
    
    /**
     * @Route("/replace/{replace}/with/{replacement}", name="replace_file")
     */
    public function replaceFileAction($replace, $replacement)
    {
        $appResponse = new AppResponse();

        /** @var $repository \Enginewerk\EmissionBundle\Entity\FileRepository **/
        $repository = $this->getDoctrine()->getRepository('EnginewerkEmissionBundle:File');

        /** @var $replaceFile \Enginewerk\EmissionBundle\Entity\File **/
        $replaceFile = $repository->findOneByFileId($replace);
        if (!$replaceFile) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $replace));
        }

        /** @var $replacementFile \Enginewerk\EmissionBundle\Entity\File **/
        $replacementFile = $repository->findOneByFileId($replacement);
        if (!$replacementFile) {
            throw $this->createNotFoundException(sprintf('File #%s not found.', $replace));
        }

        if ($replaceFile->getUploadedBy() == $replacementFile->getUploadedBy()) {

            $em = $this->getDoctrine()->getManager();
            $replacementFile->setFileId($replaceFile->getFileId());
            $em->remove($replaceFile);

            try {
                $em->flush();
                $appResponse->success('File replaced.');
            } catch (Exception $ex) {
                $this->get('logger')->error(sprintf('Can`t replace file. [%s] %s', get_class($ex), $ex->getMessage()));
                $appResponse->error(sprintf('Can`t replace file.'));
            }

        } else {
            $appResponse->error(sprintf('Only owner can replace file.'));
        }

        return new JsonResponse($appResponse->response());
    }
}