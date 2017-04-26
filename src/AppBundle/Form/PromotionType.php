<?php

namespace AppBundle\Form;

use AppBundle\Entity\Promotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('percent',PercentType::class,['type'=>'integer'])
            ->add('startDate',DateType::class,['widget' => 'single_text'])
            ->add('endDate',DateType::class,['widget' => 'single_text']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>Promotion::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_promotion_type';
    }
}
