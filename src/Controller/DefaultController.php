<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 3:56 PM
 */

namespace App\Controller;


use App\Service\ActivityManager;
use App\Service\BookManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    private $bookManager;
    private $activityManager;

    public function __construct(
        BookManager $bookManager,
        ActivityManager $activityManager
    )
    {
        $this->bookManager = $bookManager;
        $this->activityManager = $activityManager;
    }

    /**
     * @return Response
     */
    public function index()
    {
        return $this->render(
            'index.html.twig',
            [
                'popularBooks' => $this->bookManager->getPopularBooks(),
                'newBooks' => $this->bookManager->getNewestBooks(),
                'recentActivity' => $this->activityManager->findAllActivity(7),
            ]
        );
    }
}
