<?php

namespace Enginewerk\EmissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\Response\AppResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $FileBlock = new FileBlock();
        $Form = $this->createFormBuilder($FileBlock)
                ->add('fileBlock', null, array('mapped' => false))
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

        $FileBlocks = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:FileBlock')
                ->findBy(array('fileId' => $File->getId()), array('rangeStart' => 'ASC'));

        $blocks = array();
        foreach ($FileBlocks as $FileBlock) {
            $Block = $this->getDoctrine()
                ->getRepository('EnginewerkEmissionBundle:BinaryBlock')->findOneByChecksum($FileBlock->getFileHash());

            $filePath = $Block->getPathname();

            if (!file_exists($filePath) || is_dir($filePath)) {
                throw $this->createNotFoundException('File doesn`t exists');
            }

            $blocks[] = $filePath;
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

        $response->sendHeaders();

        $response->setCallback(function () use ($blocks) {
            foreach ($blocks as $blockFilePath) {
                readfile($blockFilePath);
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

            } catch (Exception $ex) {
                $appResponse->error('Can`t remove File');
                $this->get('logger')->error(sprintf('Can`t remove File #%s. %s', $File->getId(), $ex->getMessage()));
            }
        }

        return new JsonResponse($appResponse->response(), 200);
    }
}
