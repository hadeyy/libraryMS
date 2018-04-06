<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 4/6/2018
 * Time: 11:29 AM
 */

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'pass123',
        ]);
    }

    /**
     * @dataProvider activityRouteProvider
     * @param $route
     */
    public function testAdminRoutesAreSuccessful($route)
    {
        $this->client->request('GET', $route);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'Route is successful.');
    }

    public function activityRouteProvider()
    {
        return [
            ['/admin/users'],
            ['/admin/activity'],
            ['/admin/activity/today'],
            ['/admin/activity/this-week'],
            ['/admin/activity/this-month'],
            ['/admin/activity/this-year'],
        ];
    }
}
