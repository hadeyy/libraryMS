<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/19/2018
 * Time: 4:04 PM
 */

namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @dataProvider uriProvider
     *
     * @param $uri
     */
    public function testDefaultRoutesAreSuccessful($uri)
    {
        $this->client->request('GET', $uri);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function uriProvider()
    {
        return [
            ['/'],
            ['/register'],
            ['/login'],
        ];
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(3, $crawler->filter('h2')->count());
        $this->assertEquals(10, $crawler->filter('div.flip-card-container')->count());
        $this->assertGreaterThanOrEqual(4, $crawler->filter('a')->count());
        $this->assertContains('Home', $this->client->getResponse()->getContent());
        $this->assertContains('Catalog', $this->client->getResponse()->getContent());
        $this->assertContains('Newest books', $this->client->getResponse()->getContent());
        $this->assertContains('Most popular books', $this->client->getResponse()->getContent());
    }
}
