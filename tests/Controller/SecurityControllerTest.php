<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 4/6/2018
 * Time: 11:24 AM
 */

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'pass123',
        ]);

        $client->request('GET', '/login');
        $this->assertTrue(
            $client->getResponse()->isRedirect(),
            'Route redirects already authenticated users.'
        );
    }
}
