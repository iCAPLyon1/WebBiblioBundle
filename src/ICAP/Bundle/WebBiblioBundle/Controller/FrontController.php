<?php

namespace ICAP\Bundle\WebBiblioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/web-biblio")
 */
class FrontController extends Controller
{
    /**
     * @Route("/", name="web_biblio_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/add", name="web_biblio_add")
     * @Method("POST")
     * @Template()
     */
    public function addAction()
    {
        return array();
    }

    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="web_biblio_remove")
     * @Method("POST, DELETE")
     * @Template()
     */
    public function removeAction($id)
    {
        return array();
    }

    /**
     * @Route("/publish/{id}", requirements={"id" = "\d+"}, name="web_biblio_publish")
     * @Method("POST, PUT")
     * @Template()
     */
    public function publishAction($id)
    {
        return array();
    }

    /**
     * @Route("/unpublish/{id}", requirements={"id" = "\d+"}, name="web_biblio_unpublish")
     * @Method("POST, PUT")
     * @Template()
     */
    public function unpublishAction($id)
    {
        return array();
    }
}
