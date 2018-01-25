<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/25/2018
 * Time: 1:38 PM
 */

namespace App\Form;


use App\Entity\BookReservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y')),
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y') + 1),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BookReservation::class,
        ]);
    }
}
