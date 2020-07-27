<?php

namespace App\Form;

use App\Entity\DeliveryOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DeliveryOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname',TextType::class,[
                'required' => false
            ])
            ->add('lastname',TextType::class,[
                'required' => false
            ])
            ->add('adress',TextType::class,[
                'required' => false
            ])
            ->add('adress_complement',TextType::class,[
                'required' => false
            ])
            ->add('city',TextType::class,[
                'required' => false
            ])
            ->add('zip_code',TextType::class,[
                'required' => false
            ])
            ->add('country', ChoiceType::class, [
                'choices' =>[
                    'France' => "France",
                    'Belgique' => "Belgique",
                    'Luxembourg' => "Luxembourg"
                ],
                'required' => false
            ])
            ->add('phone_number',TextType::class,[
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DeliveryOrder::class,
        ]);
    }
}
