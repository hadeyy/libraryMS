<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 2:01 PM
 */

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;

class EntityManager
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function save($entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
