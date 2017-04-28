<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',TextType::class)
            ->add('money',MoneyType::class,['currency'=>'USD'])
            ->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event){
                $user=$event->getData();
                $form=$event->getForm();
                $userRole = $user->getHighestRole();
                if ($userRole != 'Administrator') {
                    $form->add('promote', SubmitType::class);
                }
                if ($userRole != 'User') {
                    $form->add('demote', SubmitType::class);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'=>User::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_user_edit_type';
    }
}
