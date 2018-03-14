<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/16/2018
 * Time: 12:52 PM
 */

namespace App\Form;


use App\Entity\Author;
use App\Entity\Genre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Valid;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Title must be at least {{ limit }} characters long.',
                        'max' => 140,
                        'maxMessage' => 'Title cannot be longer than {{ limit }} characters.',
                    ])
                ],
            ])
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'placeholder' => '- Choose author -',
                'constraints' => [
                    new NotBlank(),
                    new Valid(),
                ],
            ])
            ->add('cover', FileType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Image([
                        'minWidth' => 50,
                        'minWidthMessage' => 'Minimum width expected is {{ min_width }}px.',
                        'maxWidth' => 5000,
                        'maxWidthMessage' => 'Allowed maximum width is {{ max_width }}px.',
                        'minHeight' => 50,
                        'minHeightMessage' => 'Minimum height expected is {{ min_height }}px.',
                        'maxHeight' => 5000,
                        'maxHeightMessage' => 'Allowed maximum height is {{ max_height }}px.',
                    ]),
                ],
            ])
            ->add('annotation', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 140,
                        'minMessage' => 'Annotation must be at least {{ limit }} characters long.',
                        'max' => 2000,
                        'maxMessage' => 'Annotation cannot be longer than {{ limit }} characters.',
                    ])
                ],
            ])
            ->add('ISBN', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Expression([
                        'expression' => 'strlen(value) == 10 or strlen(value) == 13',
                        'message' => 'ISBN must be either 10 or 13 characters long.',
                    ])
                ],
            ])
            ->add('pages', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'integer',
                        'message' => 'The value {{ value }} is not a valid {{ type }}.',
                    ]),
                    new Range([
                        'min' => 10,
                        'minMessage' => 'This value should be greater than or equal to {{ limit }}.',
                        'max' => 13095,
                        'maxMessage' => 'This value should be less than or equal to {{ limit }}.',
                    ]),
                ],
            ])
            ->add('language', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Language name must be at least {{ limit }} characters long.',
                        'max' => 20,
                        'maxMessage' => 'Language name cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('genres', EntityType::class, [
                'class' => Genre::class,
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'placeholder' => '- Choose genre(s) -',
                'constraints' => [
                    new NotBlank(),
                    new Count([
                        'min' => 1,
                        'minMessage' => 'You must specify at least one genre.',
                        'max' => 5,
                        'maxMessage' => 'You cannot specify more than {{ limit }} genres.',
                    ])
                ],
            ])
            ->add('publisher', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Publisher name must be at least {{ limit }} characters long.',
                        'max' => 140,
                        'maxMessage' => 'Publisher name cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('publicationDate', DateType::class, [
                'widget' => 'choice',
                'years' => range(date('Y') - 150, date('Y')),
                'constraints' => [
                    new NotBlank(),
                    new Date(),
                    new LessThan('today'),
                ],
            ])
            ->add('availableCopies', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Type([
                        'type' => 'integer',
                        'message' => 'The value {{ value }} is not a valid {{ type }}.',
                    ]),
                    new Range([
                        'min' => 1,
                        'minMessage' => 'This value should be greater than or equal to {{ limit }}.',
                        'max' => 100,
                        'maxMessage' => 'This value should be less than or equal to {{ limit }}.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
