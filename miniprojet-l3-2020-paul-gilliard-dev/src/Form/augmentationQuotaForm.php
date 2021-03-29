<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class augmentationQuotaForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $value=$options['data'];
       $data= $value[0];

        $builder
            ->add('formule',ChoiceType::class,array(
                'choices' => array(
                    '1' => '1',
                    '10' => '10',
                    '100' => '100'
                ),
                'data' => $data,
                'constraints' =>array(
                    new NotEqualTo([
                        'value'=> $data,
                    'message' => "C'est déjà ton abonnement actuel"
                            ]
    )
            )));


    }
}