<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/26/2018
 * Time: 4:03 PM
 */

namespace App\Controller\catalog;


use App\Entity\Author;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends AbstractController
{
    /**
     * @param Author $author
     *
     * @ParamConverter("author", class="App\Entity\Author")
     *
     * @return Response
     */
    public function showAuthor(Author $author)
    {
        return $this->render(
            'catalog/author/show.html.twig',
            [
                'author' => $author,
            ]
        );
    }
}
