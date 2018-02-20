<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/20/2018
 * Time: 11:21 AM
 */

namespace App\Tests\Controller\librarian;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LibraryControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([],[
            'PHP_AUTH_USER' => 'librarian',
            'PHP_AUTH_PW' => 'kitten',
        ]);
    }

    /**
     * @dataProvider uriProvider
     *
     * @param $uri
     */
    public function testLibrarianRoutesAreSuccessful($uri)
    {
        $this->client->request('GET', $uri);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function uriProvider()
    {
        return [
            ['/catalog/books/new'],
            ['/catalog/authors/new'],
            ['/catalog/genres/new'],
            ['/reservations'],
        ];
    }

    public function testLibrarianRoutesAreRedirects()
    {
        $this->client->request('GET', '/reservations/update/id/1/reading');
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }
}
