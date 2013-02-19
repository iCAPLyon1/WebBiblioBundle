<?php

namespace ICAP\Bundle\WebBiblioBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * -----------------------------------------------------------------------------------
     *                              web_biblio_all : /
     * -----------------------------------------------------------------------------------
     */

    public function testAll1()
    {
        $client = static::createClient();
        $client->insulate();
        $crawler = $client->request('POST', '/');

        $this->assertGreaterThan(0, $crawler->filter('html:contains("405")')->count());
    }

    public function testAll2()
    {
        $client = static::createClient();
        $client->insulate();
        $crawler = $client->request('GET', '/');

        $this->assertGreaterThan(0, $crawler->filter('a[href^="/login"]')->count());
    }

    /**
     * -----------------------------------------------------------------------------------
     *                              web_biblio_index : /web-biblio
     * -----------------------------------------------------------------------------------
     */

    public function testUserList1()
    {
        $client = static::createClient();
        $client->insulate();
        $crawler = $client->request('POST', '/web-biblio');

        $this->assertGreaterThan(0, $crawler->filter('html:contains("405")')->count());
    }

    public function testUserList2()
    {
        $client = static::createClient();
        $client->insulate();
        $crawler = $client->request('GET', '/web-biblio');

        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserList3()
    {
        $client = static::createClient();
        $client->insulate();
        $crawler = $client->request('GET', '/web-biblio');
        $response = $client->getResponse();

        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }
}
