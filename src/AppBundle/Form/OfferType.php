<?php

namespace AppBundle\Form;

use AppBundle\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class)
            ->add('price',MoneyType::class,['currency'=>'USD'])
            ->add('description',TextareaType::class)
            ->add('category',null,['placeholder'=>'--Select a category--'])
            ->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event){
                $form=$event->getForm();
                $offerQuantity=$event->getData()->getQuantity();
                $quantity = [];

                for ($i = 1; $i <= $offerQuantity; $i++) {
                    $quantity[$i] = $i;
                }

                $form->add(
                    'quantity',
                    ChoiceType::class,
                    [
                        'choices' => $quantity,
                    ]
                );
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>Offer::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_offer_type';
    }
}
