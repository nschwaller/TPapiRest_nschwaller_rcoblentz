<?php
 
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UrssafFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void // Add the ': void' return type here
    {
        $builder
            ->add('salaire_brut', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Salaire Brut (€ par mois)'
            ])
            ->add('contrat', ChoiceType::class, [
                'choices' => [
                    'CDI' => 'CDI',
                    'Stage' => 'stage',
                    'Alternance' => 'apprentissage',
                    'CDD' => 'CDD',
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
