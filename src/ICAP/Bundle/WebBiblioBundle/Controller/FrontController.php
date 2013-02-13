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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * @Route("/web-biblio")
 */
class FrontController extends Controller
{
    /**Method that redirects to page given a weblink Id
     *
     *
     */
    protected function goToPageByWebLinkId($username, $id)
    {
        $page = 1;
        if($id>0){
            $idx = $this->get("icap_webbiblio.manager")->getWebLinkIndexInList($username, $id);
            if($idx && $idx>0){
                $page = ceil($idx/$this->container->getParameter('nb_web_link_by_page'));
            }
        }

        return $this->redirect($this->generateUrl('web_biblio_userlist', array('page' => $page)));
    }

    /**
     * @Route("/", name="web_biblio_index", defaults={"page" = 1})
     * @Route("/{page}", name="web_biblio_userlist", requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @Template()
     */
    public function userlistAction($page)
    {
        $logger = $this->get('logger');
        $logger->debug('userlistAction()');


        $username = $this->getUser()->getEmail();
        $logger->debug('username: '.$username);

        $adapter  = new DoctrineORMAdapter($this->get("icap_webbiblio.manager")->getListQueryBuilder($username));
        $pager    = new PagerFanta($adapter);

        $pager->setMaxPerPage($this->container->getParameter('nb_web_link_by_page'));

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }
        $form = $this->createForm(new WebLinkType());

        return array(
            'form' => $form->createView(),
            'pager' => $pager
        );
    }

    
    /**
     * @Route("/all", name="web_biblio_all", defaults={"page" = 1})
     * @Route("/all/{page}", name="web_biblio_all_paginated", requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @Template()
     */
    public function allAction($page)
    {
        $logger = $this->get('logger');
        $logger->debug('allAction()');

        $adapter  = new DoctrineORMAdapter($this->get("icap_webbiblio.manager")->getPublishedWebLinksQueryBuilder());
        $pager    = new PagerFanta($adapter);

        $pager->setMaxPerPage($this->container->getParameter('nb_web_link_by_page'));

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return array(
            'pager' => $pager
        );
    }

    /**
     * @Route("/add", name="web_biblio_add")
     * @Method({"POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        $username = $this->getUser()->getEmail();
        //Uses parameters in post (url, username, published, tags) to create a new register.
        //Returns success or error
        $webLink = new WebLink();
        $form = $this->createForm(new WebLinkType(), $webLink);

        $form->bind($request);
        // Adding session var for complete entity
        $webLink->setUsername($username);
       
        if ($form->isValid()) {
            $webLink = $this->get("icap_webbiblio.manager")->updateWebLink($webLink);
            $request->getSession()->getFlashBag()->add('icap_webbiblio_success', 'WebLink added!');
        } else{
            //Display error!
            $request->getSession()->getFlashBag()->add('icap_webbiblio_error', 'Invalid form! WebLink not added...');
        }
        
        return $this->goToPageByWebLinkId($username, $webLink->getId());
    }

    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="web_biblio_remove")
     * @Method({"POST", "DELETE"})
     * @Template()
     */
    public function removeAction($id, Request $request)
    {
        $username = $this->getUser()->getEmail();
        //Deletes register using its id ($id)
        //Returns success or error
        $em = $this->getDoctrine()->getEntityManager();
        $webLink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOneBy(array('id' => $id));
        
        if (!$webLink) {
            throw $this->createNotFoundException(
                'No register found for id '.$id
            );
        } else {
            $webLinks = $this->get("icap_webbiblio.manager")->removeWebLink($webLink);
        }
        $request->getSession()->getFlashBag()->add('icap_webbiblio_success', 'WebLink "'.$webLink->getUrl().'" deleted');

        return $this->redirect($this->generateUrl('web_biblio_userlist'));       
    }

    /**
     * @Route("/publish/{id}/{value}", requirements={"id" = "\d+", "value" = "0|1"}, name="web_biblio_publish")
     * @Method({"POST"})
     * @Template()
     */
    public function setPublishedAction($id, $value, Request $request)
    {
        $username = $this->getUser()->getEmail();
        //Publishes a register using its id ($id)
        //Returns success or error
        $em = $this->getDoctrine()->getEntityManager();
        $webLink = $em->getRepository('ICAPWebBiblioBundle:WebLink')->findOneBy(array('id' => $id));
        
        if (!$webLink) {
            throw $this->createNotFoundException(
                'No register found for id '.$id
            );
        } else {
            $webLinks = $this->get("icap_webbiblio.manager")->publishWebLink($webLink, $value);
        }

        if ($value) {
            $request->getSession()->getFlashBag()->add('icap_webbiblio_success', 'WebLink "'.$webLink->getUrl().'" published');
        } else {
            $request->getSession()->getFlashBag()->add('icap_webbiblio_success', 'WebLink "'.$webLink->getUrl().'" unpublished');
        }

        return $this->goToPageByWebLinkId($username, $webLink->getId());
    }
}
