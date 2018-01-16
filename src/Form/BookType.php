<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/16/2018
 * Time: 12:52 PM
 */

namespace App\Form;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'placeholder' => '- Choose author -'
            ])
            ->add('cover', FileType::class)
            ->add('annotation', TextareaType::class, [
                'attr' => ['class' => 'tinymce']
            ])
            ->add('ISBN', TextType::class)
            ->add('pages', IntegerType::class)
            ->add('language', TextType::class)
            ->add('genres', EntityType::class, [
                'class' => Genre::class,
                'multiple' => true,
                'expanded' => true,
                'placeholder' => '- Choose genre(s) -'
            ])
            ->add('publisher', TextType::class)
            ->add('publicationDate', DateType::class, [
                'widget' => 'choice',
                'years' => range(date('Y') - 150, date('Y')),
            ])
            ->add('availableCopies', IntegerType::class)
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
