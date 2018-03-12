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
    public function testCreateAddsDataToGenre()
    {
        $genreManager = $this->getMockBuilder(GenreManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $data = ['name' => 'genre'];
        $genre = $genreManager->create($data);

        $this->assertTrue(
            $genre instanceof Genre,
            'Result is an instance of Genre class.'
        );
        $this->assertEquals('genre', $genre->getName(),'Result matches expected.');
    }

    public function testSaveCallsEntityManager()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Genre::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $genreManager = $this->getMockBuilder(GenreManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['save'])
            ->getMock();

        $genreManager->save(new Genre('name'));
    }
}
