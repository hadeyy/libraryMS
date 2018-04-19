<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/1/2018
 * Time: 10:28 AM
 */

namespace App\Tests\Service;


use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\CommentManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentManagerTest extends WebTestCase
{
    public function testCreate()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Comment::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $commentManager = new CommentManager($doctrine);

        $user = $this->createMock(User::class);
        $book = $this->createMock(Book::class);
        $commentManager->create($user, $book, 'content');
    }
}
