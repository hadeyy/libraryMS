<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 2:01 PM
 */

namespace App\Service;


use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityManager
{
    protected $em;
    protected $container;
    protected $doctrine;

    public function __construct(
        EntityManagerInterface $manager,
        ContainerInterface $container
    ) {
        $this->em = $manager;
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');
    }

    /**
     * @param object $entity The instance to make managed and persistent.
     */
    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * @param string $entity The name of the persistent object.
     * @return ObjectRepository
     */
    public function getRepository($entity)
    {
        return $this->doctrine->getRepository($entity);
    }
}
