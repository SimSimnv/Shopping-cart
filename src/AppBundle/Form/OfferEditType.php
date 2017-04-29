<?php

namespace AppBundle\Form;

use AppBundle\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            })
            ->add('isFeatured');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>Offer::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_offer_edit_type';
    }
}
