<?php

namespace JamboBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/*
 * Form type
 */
class SearchType extends AbstractType
{
    /** @const string */
    const CHOICE_ALL = 'all';

    /** @const string */
    const CHOICE_PARTICIPANT = 'participant';

    /** @const string */
    const CHOICE_PATROL = 'patrol';

    /** @const string */
    const CHOICE_TROOP = 'troop';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        unset($options);

        $builder
            ->add('type', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'choices' => [
                    'admin.all' => self::CHOICE_ALL,
                    'admin.participant' => self::CHOICE_PARTICIPANT,
                    'admin.patrol' => self::CHOICE_PATROL,
                    'admin.troop' => self::CHOICE_TROOP,
                ],
                'label' => 'admin.search.type',
            ])
            ->add('query', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'admin.search.query',
            ])
            ->add('search', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'label' => 'admin.search.submit',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'search';
    }
}
