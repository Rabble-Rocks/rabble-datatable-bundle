<?php

namespace Rabble\DatatableBundle\Filter;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericSelectFilter extends GenericFilter
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder->add($this->options['name'], ChoiceType::class, [
            'label' => $this->options['label'],
            'required' => false,
            'choices' => $this->options['choices'],
            'translation_domain' => $this->options['translationDomain'],
            'choice_translation_domain' => $this->options['choiceTranslationDomain'],
        ]);
    }

    /**
     * @return OptionsResolver|void
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'exactMatch' => true,
            'choiceTranslationDomain' => 'messages',
        ]);
        $resolver->setRequired(['choices']);
    }
}
