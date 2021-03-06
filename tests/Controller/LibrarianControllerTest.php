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

class LibrarianControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([],[
            'PHP_AUTH_USER' => 'librarian',
            'PHP_AUTH_PW' => 'pass123',
        ]);
    }

    /**
     * @dataProvider uriProvider
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
            ['/catalog/lorem-ipsum/lorem-ipsum/edit'],
            ['/catalog/authors/new'],
            ['/catalog/lorem-ipsum/edit'],
            ['/catalog/genres/new'],
            ['/reservations'],
            ['readers'],
        ];
    }
}
