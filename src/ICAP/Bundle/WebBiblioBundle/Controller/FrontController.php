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

/**
 * @Route("/web-biblio")
 */
class FrontController extends Controller
{
	protected function getUsernameInSession($request) {
		$username = null;
		if ($request->hasPreviousSession()) {
			$username = $request->getSession()->get('icap_webbiblio_username'); 
			if($username == '') {
				$username = null;
			}
		}

		return $username;
	}

	protected function yourNotLogged($request) {
		$logger = $this->get('logger');
		$logger->debug('yourNotLogged()');
		$request->getSession()->getFlashBag()->add('icap_webbiblio_error', "You're not logged.");

		return $this->redirect($this->generateUrl('web_biblio_index'));
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
	 * @Route("/userlist/{page}", name="web_biblio_userlist", requirements={"page" = "\d+"}, defaults={"page" = 1})
	 * @Template()
	 */
	public function userlistAction($page, Request $request)
	{
		$logger = $this->get('logger');

		$logger->debug('userlistAction()');
		$username = $this->getUsernameInSession($request);
		$logger->debug('username: '.$username);
		if ($username) {

			$adapter  = new DoctrineORMAdapter($this->get("icap_webbiblio.manager")->getListQueryBuilder($username));
			$pager    = new PagerFanta($adapter);

			$pager->setMaxPerPage($this->container->getParameter('nb_web_link_by_page'));
			//$pager->setMaxPerPage(2);

			try {
				$pager->setCurrentPage($page);
			} catch (NotValidCurrentPageException $e) {
				throw new NotFoundHttpException();
			}
			$form = $this->createForm(new WebLinkType());
			$logger->debug('userlistAction(), view generation');

			return array(
				'form' => $form->createView(),
				'pager' => $pager,
				'username' => $username
			);
		} else {

			return $this->yourNotLogged($request);
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
			$webLink = new WebLink();
			$form = $this->createForm(new WebLinkType(), $webLink);

			$form->bind($request);
			// Adding session var for complete entity
			$webLink->setUsername($username);

			if ($form->isValid()) {
				$webLink = 
				$webLinks = $this->get("icap_webbiblio.manager")->updateWebLink($webLink);
				$request->getSession()->getFlashBag()->add('icap_webbiblio_success', 'WebLink added!');

				return $this->redirect($this->generateUrl('web_biblio_userlist'));
			} else{
				//Display error!
				$request->getSession()->getFlashBag()->add('icap_webbiblio_error', 'Invalid form! WebLink not added...');

				return $this->redirect($this->generateUrl('web_biblio_userlist'));
			}
		} else {
			$this->yourNotLogged($request);
		}
	}

	/**
	 * @Route("/remove/{id}", requirements={"id" = "\d+"}, name="web_biblio_remove")
	 * @Method({"POST", "DELETE"})
	 * @Template()
	 */
	public function removeAction($id, Request $request)
	{
		$username = $this->getUsernameInSession($request);
		if($username) {
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
		} else {
			$this->yourNotLogged($request);
		}          
	}

	/**
	 * @Route("/publish/{id}/{value}", requirements={"id" = "\d+", "value" = "0|1"}, name="web_biblio_publish")
	 * @Method({"POST"})
	 * @Template()
	 */
	public function setPublishedAction($id, $value, Request $request)
	{
		$username = $this->getUsernameInSession($request);
		if($username) {
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
		} else {
			$this->yourNotLogged($request);
		}
	}
}
