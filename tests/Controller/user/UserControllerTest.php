<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/20/2018
 * Time: 11:22 AM
 */

namespace App\Tests\Controller\user;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([],[
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'kitten',
        ]);
    }

    /**
     * @dataProvider uriProvider
     *
     * @param $uri
     */
    public function testUserRoutesAreSuccessful($uri)
    {
        $this->client->request('GET', $uri);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function uriProvider()
    {
        return [
            ['/user/profile'],
            ['/user/profile/edit'],
            ['/user/activity'],
            ['/user/notifications'],
        ];
    }

    public function testUserRoutesAreRedirects()
    {
        $this->client->request('GET', '/logout');
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }
}
