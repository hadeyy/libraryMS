<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/20/2018
 * Time: 11:21 AM
 */

namespace App\Tests\Controller\reader;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LibraryControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([],[
            'PHP_AUTH_USER' => 'reader',
            'PHP_AUTH_PW' => 'kitten',
        ]);
    }

    public function testReaderRoutesAreSuccessful()
    {
        $this->client->request('GET', '/catalog/book/1/reserve');
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testReaderRoutesAreRedirects()
    {
        $this->client->request('GET', '/catalog/book/1/toggle-favorite');
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }
}
