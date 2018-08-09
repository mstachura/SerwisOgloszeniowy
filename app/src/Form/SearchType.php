<?php
/**
 * Search form.
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class LoginType.
 */
class SearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'category_search',
            ChoiceType::class,
            [
                'label' => 'label.category_search ',
                'required' => true,
                'attr' => array('class' => 'form-control'),
                'choices' => $this->prepareCategoriesForChoices(),
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => ['search-default'],
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'phrase',
            TextType::class,
            [
                'label' => 'label.phrase',
                'required' => true,
                'attr' => [
                    'max_length' => 32,

                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'max' => 32,
                        ]
                    ),
                ],
            ]
        );

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'search_type';
    }

    protected function prepareCategoriesForChoices()
    {
        $categories = ['advertisement', 'user'];
        $choices = [];
        foreach ($categories as $category) {
            $choices[$category] = $category;
        }
        return $choices;
    }
}