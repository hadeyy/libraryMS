<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 3:56 PM
 */

namespace App\Controller;


use App\Service\ActivityManager;
use App\Service\LibraryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    private $libraryManager;
    private $activityManager;

    public function __construct(
        LibraryManager $libraryManager,
        ActivityManager $activityManager
    ) {
        $this->libraryManager = $libraryManager;
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
                'popularBooks' => $this->libraryManager->getPopularBooks(),
                'newBooks' => $this->libraryManager->getNewestBooks(),
                'recentActivity' => $this->activityManager->getRecentActivity(),
            ]
        );
    }
}
