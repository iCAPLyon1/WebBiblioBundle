<?php

namespace ICAP\Bundle\WebBiblioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use ICAP\Bundle\WebBiblioBundle\Form\WebLinkType;
use ICAP\Bundle\WebBiblioBundle\Form\LoginType;
use ICAP\Bundle\WebBiblioBundle\Entity\WebLink;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/web-biblio")
 */
class FrontController extends Controller
{
    protected function getUsernameInSession($request) {
        $username = null;
        if ($request->hasPreviousSession()) {
           $username = $request->getSession()->get('icap_webbiblio_username'); 
        }
        return $username;
    }

    /**
     * @Route("/", name="web_biblio_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $username = $this->getUsernameInSession($request);
        if ($username) {
            return $this->redirect($this->generateUrl('web_biblio_userlist'));
        } else {

            $form = $this->createForm(new LoginType());
            return array('form' => $form->createView(),);
        }
    }

    /**
     * "Connect" a user with his username : add a http session "username" variable
     *
     * @Route("/connect", name="web_biblio_connect")
     * @Method({"POST"})
     * @Template()
     */
    public function connectAction(Request $request)
    {
        $logger = $this->get('logger');
        $logger->info('connectAction()');

        $form = $this->createForm(new LoginType());
        $form->bind($request);

        if ($form->isValid()) {
            $logger->info('connectAction(form valid)');
            $username = $form->get('username')->getData();
            $logger->info('connectAction(username = '+$username+')');
            $request->getSession()->set('icap_webbiblio_username', $username);

            return $this->redirect($this->generateUrl('web_biblio_index'));
        } else {
            $request->getSession()->getFlashBag()->add('icap_webbiblio_error', 'Invalid form! Connection aborted...');

            return $this->redirect($this->generateUrl('web_biblio_index'));
        }
    }

    /**
     * "Disconnect" a user with his username : remove http session "username" variable
     *
     * @Route("/disconnect", name="web_biblio_disconnect")
     * @Method({"POST"})
     * @Template()
     */
    public function disconnectAction(Request $request)
    {
        if ($request->hasPreviousSession()) {
           $username = $request->getSession()->remove('icap_webbiblio_username'); 
        }

        return $this->redirect($this->generateUrl('web_biblio_index'));
    }

    /**
     * @Route("/userlist", name="web_biblio_userlist")
     * @Template()
     */
    public function userlistAction(Request $request)
    {
        $username = $this->getUsernameInSession($request);
        if ($username) {
            $weblinks = $this->get("icap_webbiblio.manager")->getList($username);
            $form = $this->createForm(new WebLinkType());

            return array(
                'form' => $form->createView(),
                'weblinks' => $weblinks,
                'username' => $username
            );
        } else {
            $request->getSession()->getFlashBag()->add('icap_webbiblio_error', "You're not logged.");

            return $this->redirect($this->generateUrl('web_biblio_index'));
        }
    }

    /**
     * @Route("/add", name="web_biblio_add")
     * @Method({"POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        $username = $this->getUsernameInSession($request);
        if($username) {
            //Uses parameters in post (url, username, published, tags) to create a new register.
            //Returns success or error
            $weblink = new WebLink();
            $form = $this->createForm(new WebLinkType(), $weblink);

            $form->bind($request);
            // Adding session var for complete entity
            $weblink->setUsername($username);

            if ($form->isValid()) {
                $weblink = 
                $weblinks = $this->get("icap_webbiblio.manager")->updateWebLink($weblink);
                $request->getSession()->getFlashBag()->add('icap_webbiblio_success', 'WebLink added!');

                return $this->redirect($this->generateUrl('web_biblio_userlist'));
            } else{
                //Display error!
                $request->getSession()->getFlashBag()->add('icap_webbiblio_error', 'Invalid form! WebLink not added...');

                return $this->redirect($this->generateUrl('web_biblio_userlist'));
            }
        } else {

            return $this->redirect($this->generateUrl('web_biblio_index'));
        }
    }

    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="web_biblio_remove")
     * @Method({"POST, DELETE"})
     * @Template()
     */
    public function removeAction($id, Request $request)
    {
        $username = $this->getUsernameInSession($request);
        if($username) {
            //Deletes register using its id ($id)
            //Returns success or error
            $em = $this->getDoctrine()->getEntityManager();
            $weblink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOne($id);
            
            if (!$weblink) {
                throw $this->createNotFoundException(
                    'No register found for id '.$id
                );
            } else {
                $weblinks = $this->get("icap_webbiblio.manager")->removeWebLink($weblink);
                die("Delete ok!");
            }
            
            return array();
        } else {

            return $this->redirect($this->generateUrl('web_biblio_index'));
        }          
    }

    /**
     * @Route("/publish/{id}", requirements={"id" = "\d+"}, name="web_biblio_publish")
     * @Method({"POST, PUT"})
     * @Template()
     */
    public function publishAction($id, Request $request)
    {
        $username = $this->getUsernameInSession($request);
        if($username) {
            //Publishes a register using its id ($id)
            //Returns success or error
            $em = $this->getDoctrine()->getEntityManager();
            $weblink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOne($id);
            
            if (!$weblink) {
                throw $this->createNotFoundException(
                    'No register found for id '.$id
                );
            } else {
                $weblinks = $this->get("icap_webbiblio.manager")->publishWebLink($weblink, true);
                die("Publish ok!");
            }

            return array();
        } else {

            return $this->redirect($this->generateUrl('web_biblio_index'));
        }
    }

    /**
     * @Route("/unpublish/{id}", requirements={"id" = "\d+"}, name="web_biblio_unpublish")
     * @Method({"POST, PUT"})
     * @Template()
     */
    public function unpublishAction($id)
    {
        $username = $this->getUsernameInSession($request);
        if($username) {
            //Unpublishes a register using its id ($id)
            //Returns success or error
            $em = $this->getDoctrine()->getEntityManager();
            $weblink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOne($id);
            
            if (!$weblink) {
                throw $this->createNotFoundException(
                    'No register found for id '.$id
                );
            } else {
                $weblinks = $this->get("icap_webbiblio.manager")->publishWebLink($weblink, false);
                die("Unpublish ok!");
            }
            
            return array();
        } else {

            return $this->redirect($this->generateUrl('web_biblio_index'));
        }   
    }
}
