<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            // ->add('name', ChoiceType::class, array(
            //     'choices'  => [
            //         'product0' => '1',
            //         'product1' => '2',
            //         'product2' => '3',
            //     ],
            //     'expanded' => true,
            //     'multiple' => false
            // ));
            // ->add('name', EntityType::class, array(
            //     'class' => Product::class,
            //     'expanded' => true,
            //     'multiple' => false,
            //     'choice_label' => 'name',
            //     'choice_value' => 'id',
            // ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
