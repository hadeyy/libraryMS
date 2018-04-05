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

        $genreManager->create('genre');
    }

    public function testCreateArrayFromGenre()
    {
        $genre = new Genre('test');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $genreManager = new GenreManager($doctrine);

        $result = $genreManager->createArrayFromGenre($genre);

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('test', $result['name']);
    }

    public function testChangeName()
    {
        $genre = new Genre('test');

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $genreManager = new GenreManager($doctrine);

        $genreManager->changeName($genre, 'new name');

        $this->assertEquals('new name', $genre->getName());
    }

    public function testRemove()
    {
        $genre = new Genre('test');

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(Genre::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $genreManager = new GenreManager($doctrine);
        $genreManager->remove($genre);
    }
}
