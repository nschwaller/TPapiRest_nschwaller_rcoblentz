<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UrssafFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('salaire_brut', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Salaire Brut (€ par mois)'
            ])
            ->add('contrat', ChoiceType::class, [
                'choices' => [
                    'CDI' => 'cdi',
                    'Stage' => 'stage',
                    'Alternance' => 'alternance',
                    'CDD' => 'cdd',
                ],
                'label' => 'Type de Contrat'
            ])
            ->add('calculer', SubmitType::class, [
                'label' => 'Calculer'
            ]);   
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
