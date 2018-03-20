<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/28/2018
 * Time: 4:28 PM
 */

namespace App\Tests\Service;


use App\Entity\Genre;
use App\Service\GenreManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GenreManagerTest extends WebTestCase
{
    public function testCreate()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Genre::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $genreManager = new GenreManager($doctrine);

        $genreManager->create(['name' => 'genre']);
    }
}
