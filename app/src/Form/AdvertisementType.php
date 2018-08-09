<?php
/**
 * Comment type.
 */
namespace Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
/**
 * Class CommentType.
 *
 * @package Form
 */
class AdvertisementType extends AbstractType
{
    /**
     * Build Form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'label.name',
                'required'   => true,
                'attr' => [
                    'max_length' => 32,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['ads-default']]
                    ),
                ],
            ]
        );

        if (!isset($options['data']) || !isset($options['data']['id'])) {
            $builder->add(
                'photo',
                FileType::class,
                [
                    'label' => 'label.photo',
                    'required' => false,
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Image(
                            [
                                'maxSize' => '1024k',
                                'mimeTypes' => [
                                    'image/png',
                                    'image/jpeg',
                                    'image/pjpeg',
                                    'image/jpeg',
                                    'image/pjpeg',
                                ],
                            ]
                        ),
                    ],
                ]
            );
        }

        $builder->add(
            'photo_title',
            TextareaType::class,
            [
                'label' => 'label.photo_title',
                'required'   => true,
                'attr' => [
                    'min_length' => 10,
                    'max_length' => 128,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['ads-default']]
                    ),
                ],
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label' => 'label.description',
                'required'   => true,
                'attr' => [
                    'max_length' => 255,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['ads-default']]
                    ),
                ],
            ]
        );

        $builder->add(
            'price',
            NumberType::class,
            [
                'label' => 'label.price',
                'required'   => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['ads-default']]
                    ),
                ],
            ]
        );


        $builder->add(
            'category_id',
            ChoiceType::class,
            [
                'label' => 'label.categories',
                'required' => true,
                'placeholder' => 'label.none',
                'choices' => $this->prepareCategoriesForChoices($options['category_repository']),
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'label' => 'label.type',
                'required' => true,
                'placeholder' => 'label.none',
                'choices' => $this->prepareTypesForChoices()
            ]
        );

        $builder->add(
            'location',
            TextType::class,
            [
                'label' => 'label.location',
                'required' => false,
                'attr' => [
                    'max_length' => 100,
                    'min_length' => 3
                ],
            ]
        );

        $builder->add(
            'province',
            ChoiceType::class,
            [
                'label' => 'label.province',
                'required' => true,
                'placeholder' => 'label.none',
                'choices' => $this->prepareProvinceForChoices(),
            ]
        );



    }


    /**
     * Configure Options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['ads-default'],
                'category_repository' => null,
            ]
        );
    }
    /**
     * Get Block Prefix
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'ads_type';
    }

    /**
     * Prepare Categories For Choices
     * @param $categoryRepository
     * @return array
     */
    protected function prepareCategoriesForChoices($categoryRepository)
    {
        $categories = $categoryRepository->findAll();
        $choices = [];
        foreach ($categories as $category) {
            $choices[$category['name']] = $category['id'];
        }
        return $choices;
    }

    protected function prepareTypesForChoices()
    {

        $choices = ['kupno' => 'kupno', 'wymiana' => 'wymiana', 'sprzedaż' => 'sprzedaż'];



        return $choices;
    }

    protected function prepareProvinceForChoices()
    {

        $provinces = ['małopolskie', 'wielkopolskie', 'dolnośląskie', 'kujawsko-pomorskie', 'lubelskie', 'lubuskie', 'łódzkie', 'mazowieckie', 'opolskie', 'podkarpackie', 'podlaskie', 'pomorskie', 'śląskie', 'świętokrzyskie', 'warmińsko-mazurskie', 'zachodniopomorskie'];
        $choices=[];

        foreach ($provinces as $province){
            $choices[$province] = $province;
        }
        return $choices;
    }



}