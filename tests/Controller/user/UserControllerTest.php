<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/20/2018
 * Time: 11:22 AM
 */

namespace App\Tests\Controller\user;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'kitten',
        ]);
    }

    /** @FIXME */
//    /**
//     * @dataProvider successfulUriProvider
//     * @param $uri
//     */
//    public function testUserRoutesAreSuccessful($uri)
//    {
//        $this->client->request('GET', $uri);
//        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Route is successful.');
//    }

    public function successfulUriProvider()
    {
        return [
            ['/user/profile'],
            ['/user/profile/edit'],
            ['/user/activity'],
            ['/user/notifications'],
        ];
    }

    /**
     * @dataProvider redirectRouteProvider
     *
     * @param $uri
     */
    public function testUserRoutesAreRedirects($uri)
    {
        $this->client->request('GET', $uri);
        $this->assertTrue($this->client->getResponse()->isRedirect(), 'Route is a redirect.');
    }

    public function redirectRouteProvider()
    {
        return [
            ['/catalog/book/1/toggle-favorite'],
            ['/catalog/book/1/toggle-favorite'],
            ['/logout'],
        ];
    }
}
