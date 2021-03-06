<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
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
            ->add('email')
            ->add('product', EntityType::class,[
                'class' => Product::class,
                'expanded' => true,
                'multiple' => false,
                'choice_value' => 'id',
            ])
            ->add('methodPayment', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
