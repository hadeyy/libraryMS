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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserManager $userManager
     * @param PasswordManager $passwordManager
     * @param FileManager $fileManager
     * @param EntityManager $entityManager
     * @param string $role
     * @param ContainerInterface $container
     *
     * @return Response
     */
    public function register(
        Request $request,
        UserManager $userManager,
        PasswordManager $passwordManager,
        FileManager $fileManager,
        EntityManager $entityManager,
        ContainerInterface $container,
        string $role = 'ROLE_READER'
    ) {
        $user = new User();

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
