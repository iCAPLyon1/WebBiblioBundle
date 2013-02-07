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
    /**
     * @Route("/login_test", name="web_biblio_test")
     * @Template()
     */
    public function testAction()
    {
        $logger = $this->get('logger');
        $user = $this->getUser();
        if($user) {
            $username = $user->getUsername();
            $logger->debug('username: '.$username);
            die("Logged: ".$username);
        }else {
            die("not Logged");
        }

        return array();
    }

    /**
     * @Route("/login", name="web_biblio_login")
     * @Template()
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            // last username entered by the user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        );
    }

    /**
     * @Route("/login_redirect", name="web_biblio_not_logged")
     * @Template()
     */
    public function notLoggedAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $session->getFlashBag()->add('icap_webbiblio_error', "You 're not logged!");

       return $this->redirect($this->generateUrl('web_biblio_login'));
    }

    /**
     * Connect a user. Supported by symfony2.
     * See app/config/security.yml
     *
     * @Route("/login_check", name="web_biblio_connect")
     * @Template()
     */
    public function connectAction()
    {
        return array();
    }

    /**
     * Disconnect a user. Supported by symfony2
     * See app/config/security.yml
     *
     * @Route("/disconnect", name="web_biblio_disconnect")
     * @Template()
     */
    public function disconnectAction()
    {
        return array();
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
        
        $username = $this->getUser()->getUsername();
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
     * @Route("/add", name="web_biblio_add")
     * @Method({"POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        $username = $this->getUser()->getUsername();
        //Uses parameters in post (url, username, published, tags) to create a new register.
        //Returns success or error
        $webLink = new WebLink();
        $form = $this->createForm(new WebLinkType(), $webLink);

        $form->bind($request);
        // Adding session var for complete entity
        $webLink->setUsername($username);

        if ($form->isValid()) {
            $webLink = 
            $webLinks = $this->get("icap_webbiblio.manager")->updateWebLink($webLink);
            $request->getSession()->getFlashBag()->add('icap_webbiblio_success', 'WebLink added!');
        } else{
            //Display error!
            $request->getSession()->getFlashBag()->add('icap_webbiblio_error', 'Invalid form! WebLink not added...');
        }

        return $this->redirect($this->generateUrl('web_biblio_userlist'));
    }

    /**
     * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="web_biblio_remove")
     * @Method({"POST", "DELETE"})
     * @Template()
     */
    public function removeAction($id, Request $request)
    {
        $username = $this->getUser()->getUsername();
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
        $username = $this->getUser()->getUsername();
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

        return $this->redirect($this->generateUrl('web_biblio_userlist'));
    }
}
