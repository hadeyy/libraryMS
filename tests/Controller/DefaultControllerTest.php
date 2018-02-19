<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/19/2018
 * Time: 4:04 PM
 */

namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(3, $crawler->filter('h2')->count());
        $this->assertEquals(10, $crawler->filter('div.flip-card-container')->count());
        $this->assertGreaterThanOrEqual(4, $crawler->filter('a')->count());
        $this->assertContains('Home', $client->getResponse()->getContent());
        $this->assertContains('Catalog', $client->getResponse()->getContent());
        $this->assertContains('Newest books', $client->getResponse()->getContent());
        $this->assertContains('Most popular books', $client->getResponse()->getContent());
    }
}
