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
    public function testCreateAddsUserAndBookToComment()
    {
        $user = new User();
        $book = new Book();

        $commentManager = $this->getMockBuilder(CommentManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $comment = $commentManager->create($user, $book);

        $this->assertTrue(
            $comment instanceof Comment,
            'Result is an instance of Comment class.'
        );
        $this->assertEquals($user, $comment->getAuthor(), 'Comment author matches expected.');
        $this->assertEquals($book, $comment->getBook(), 'Comment book matches expected.');

        return $comment;
    }

    /**
     * @depends testCreateAddsUserAndBookToComment
     *
     * @param Comment $comment
     */
    public function testSaveCallsEntityManager(Comment $comment)
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Comment::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $commentManager = $this->getMockBuilder(CommentManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['save'])
            ->getMock();

        $commentManager->save($comment);
    }
}
