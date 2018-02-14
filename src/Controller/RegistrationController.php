<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 2:01 PM
 */

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Service\EntityManager;
use App\Service\FileManager;
use App\Service\PasswordManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    private $user;
    protected $container;

    public function __construct()
    {
        $this->user = new User();
        $this->container = new ContainerBuilder();
    }

    /**
     * @param Request $request
     * @param UserManager $userManager
     * @param PasswordManager $passwordManager
     * @param FileManager $fileManager
     * @param EntityManager $entityManager
     * @param string $role
     *
     * @return Response
     */
    public function register(
        Request $request,
        UserManager $userManager,
        PasswordManager $passwordManager,
        FileManager $fileManager,
        EntityManager $entityManager,
        string $role = 'ROLE_READER'
    ) {
        $user = $this->user;
        $container = $this->container;

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $user->getPhoto();

            $filename = $fileManager->upload($file, $container->getParameter('user_photo_directory'));
            $password = $passwordManager->encode($user);

            $userManager->register($user, $filename, $password, $role);
            $entityManager->save($user);

            return $this->redirectToRoute('registered');
        }

        return $this->render(
            'registration/registration.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function registrationSuccess()
    {
        return $this->render('registration/registered.html.twig');
    }
}
