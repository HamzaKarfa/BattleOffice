<?php

namespace App\Form;

use App\Entity\DeliveryOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DeliveryOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('adress')
            ->add('adress_complement')
            ->add('city')
            ->add('zip_code')
            ->add('country', ChoiceType::class, [
                'choices' =>[
                    'France' => "France",
                    'Belgique' => "Belgique",
                    'Luxembourg' => "Luxembourg"
                ]
            ])
            ->add('phone_number')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryOrder::class,
        ]);
    }
}
