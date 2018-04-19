<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 4/6/2018
 * Time: 11:49 AM
 */

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CatalogControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @dataProvider catalogRouteProvider
     * @param $route
     */
    public function testCatalogRoutesAreSuccessful($route)
    {
        $this->client->request('GET', $route);
        $this->assertTrue($this->client->getResponse()->isSuccessful(),'Route is successful.');
    }

    public function catalogRouteProvider()
    {
        return [
            ['/catalog/books/1'],
            ['/catalog/books-by-author/lorem-ipsum/1'],
            ['/catalog/books-by-genre/fantasy/1'],
            ['/catalog/author/lorem-ipsum'],
        ];
    }

    public function testShowBook()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'pass123',
        ]);
        $client->request('GET', '/catalog/books/lorem-ipsum/lorem-ipsum');
        $this->assertTrue(
            $client->getResponse()->isSuccessful(),
            'Route is successful.'
        );
    }
}
