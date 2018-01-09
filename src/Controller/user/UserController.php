<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 1:43 PM
 */

namespace App\Controller\user;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction(): Response
    {


        return $this->render('user/profile.html.twig');
    }

    /**
     * @Route("/activity", name="activity")
     */
    public function activityAction(): Response
    {


        return $this->render('user/activities.html.twig');
    }

    /**
     * @Route("/notifications", name="notifications")
     */
    public function notificationAction(): Response
    {


        return $this->render('user/notifications.html.twig');
    }
}
