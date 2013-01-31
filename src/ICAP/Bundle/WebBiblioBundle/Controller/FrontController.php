<?php

namespace ICAP\Bundle\WebBiblioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ICAP\Bundle\WebBiblioBundle\Form\WebLinkType;
use ICAP\Bundle\WebBiblioBundle\Entity\WebLink;
use Symfony\Component\HttpFoundation\Request;

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
        //Displays the page with all necessary fields (form for email, url and weblink creation)

        $weblinks = $this->get("icap_webbiblio.manager")->getList(null);

        $form = $this->createForm(new WebLinkType());

        return array(
            'form' => $form->createView(),
            'weblinks' => $weblinks,
        );
    }

    /**
     * @Route("/userlist", name="web_biblio_userlist")
     * @Template()
     */
    public function userListAction()
    {
        //Displays the page with all necessary fields (form for email, url and weblink creation)

        $weblinks = $this->get("icap_webbiblio.manager")->getList();

        $form = $this->createForm(new WebLinkType());

        return array(
            'form' => $form->createView(),
            'weblinks' => $weblinks,
        );
    }

    /**
     * @Route("/add", name="web_biblio_add")
     * @Method("POST")
     * @Template()
     */
    public function addAction(Request $request)
    {
        //Uses parameters in post (url, username, published, tags) to create a new register.
        //Returns success or error
        $weblink = new WebLink();
        $form = $this->createForm(new WebLinkType(), $weblink);

        $form->bind($request);

        if ($form->isValid()) {
            $weblinks = $this->get("icap_webbiblio.manager")->updateWebLink($weblink);
            die("OK!");

            return $this->redirect($this->generateUrl('task_success'));
        }else{
            //Display error!
            die("Error!");

            return $this->redirect($this->generateUrl('task_error'));
        }
    }

    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="web_biblio_remove")
     * @Method("POST, DELETE")
     * @Template()
     */
    public function removeAction($id)
    {
        //Deletes register using its id ($id)
        //Returns success or error
        $em = $this->getDoctrine()->getEntityManager();
        $weblink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOne($id);
        
        if (!$weblink) {
            throw $this->createNotFoundException(
                'No register found for id '.$id
            );
        }else{
            $weblinks = $this->get("icap_webbiblio.manager")->removeWebLink($weblink);
            die("Delete ok!");
        }
        
        return array();
    }

    /**
     * @Route("/publish/{id}", requirements={"id" = "\d+"}, name="web_biblio_publish")
     * @Method("POST, PUT")
     * @Template()
     */
    public function publishAction($id)
    {
        //Publishes a register using its id ($id)
        //Returns success or error
        $em = $this->getDoctrine()->getEntityManager();
        $weblink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOne($id);
        
        if (!$weblink) {
            throw $this->createNotFoundException(
                'No register found for id '.$id
            );
        }else{
            $weblinks = $this->get("icap_webbiblio.manager")->publishWebLink($weblink, true);
            die("Publish ok!");
        }

        return array();
    }

    /**
     * @Route("/unpublish/{id}", requirements={"id" = "\d+"}, name="web_biblio_unpublish")
     * @Method("POST, PUT")
     * @Template()
     */
    public function unpublishAction($id)
    {
        //Unpublishes a register using its id ($id)
        //Returns success or error
        $em = $this->getDoctrine()->getEntityManager();
        $weblink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOne($id);
        
        if (!$weblink) {
            throw $this->createNotFoundException(
                'No register found for id '.$id
            );
        }else{
            $weblinks = $this->get("icap_webbiblio.manager")->publishWebLink($weblink, false);
            die("Unpublish ok!");
        }
        
        return array();
    }
}
