<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/25/2018
 * Time: 1:38 PM
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class BookReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y')),
                'constraints' => [
                    new NotBlank(),
                    new Date(),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'Earliest book reservation start date can be today.',
                    ])
                ]
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y') + 1),
                'constraints' => [
                    new NotBlank(),
                    new Date(),
                    new Expression([
                        'expression' => 'this.getDateFrom() < value',
                        'message' => 'End date cannot be before start date!',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
