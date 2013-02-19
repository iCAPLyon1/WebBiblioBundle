<?php

namespace ICAP\Bundle\WebBiblioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/web-biblio/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/", name="web_biblio_api")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $queryParams = $request->query->all();
        $format = isset($queryParams['format'])?$queryParams['format']:"json";
		$entities = $this->get('icap_webbiblio.manager')->searchWeblink($queryParams);
    	$export = $this->get('idci_exporter.manager')->export($entities, $format, $queryParams);
        
		$response = new Response();
        $response->setContent($export->getContent());
        $response->headers->set('Content-Type', $export->getContentType());

        return $response;
    }

    /**
     * @Route("/tag", name="web_biblio_api_search_tag")
     * @Method("GET")
     * @Template()
     */
    public function tagAction(Request $request)
    {
        $queryParams = $request->query->all();
        $format = isset($queryParams['format'])?$queryParams['format']:"json";
        $entities = $this->get('icap_webbiblio.manager')->searchTag($queryParams);
        $export = $this->get('idci_exporter.manager')->export($entities, $format, $queryParams);
        
        $response = new Response();
        $response->setContent($export->getContent());
        $response->headers->set('Content-Type', $export->getContentType());

        return $response;
    }
}
