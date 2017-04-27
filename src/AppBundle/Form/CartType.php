<?php

namespace AppBundle\Form;

use AppBundle\Entity\Cart;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('purchases', CollectionType::class, array(
            'entry_type' => CartOfferType::class,
            'label'=>false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([Cart::class]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_cart_type';
    }
}
