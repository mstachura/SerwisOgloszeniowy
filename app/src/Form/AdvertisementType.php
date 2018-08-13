<?php
/**
 * Advertisement type.
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
 * Class AdvertisementType.
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
            'province',
            ChoiceType::class,
            [
                'label' => 'label.province',
                'required' => true,
                'placeholder' => 'label.none',
                'choices' => $this->prepareProvinceForChoices(),
            ]
        );

        $builder->add(
            'type_id',
            ChoiceType::class,
            [
                'label' => 'label.type',
                'required' => true,
                'placeholder' => 'label.none',
                'choices' => $this->prepareTypesForChoices($options['type_repository']),
            ]
        );

        $builder->add(
            'location_id',
            ChoiceType::class,
            [
                'label' => 'label.location',
                'required' => false,
                'placeholder' => 'label.none',
                'choices' => $this->prepareLocationsForChoices($options['location_repository']),
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
                'location_repository' => null,
                'type_repository' => null
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

    /**
     * Prepare Locations For Choices
     * @param $locationRepository
     * @return array
     */
    protected function prepareLocationsForChoices($locationRepository)
    {
        $locations = $locationRepository->findAll();
        $choices = [];
        foreach ($locations as $location) {
            $choices[$location['name']] = $location['id'];
        }
        return $choices;
    }

    /**
     * Prepare Types For Choices
     * @param $typeRepository
     * @return array
     */
    protected function prepareTypesForChoices($typeRepository)
    {
        $types = $typeRepository->findAll();
        $choices = [];
        foreach ($types as $type) {
            $choices[$type['name']] = $type['id'];
        }
        return $choices;
    }

//    protected function prepareTypesForChoices()
//    {
//
//        $choices = ['kupno' => 'kupno', 'wymiana' => 'wymiana', 'sprzedaż' => 'sprzedaż'];
//
//
//
//        return $choices;
//    }

    /**
     * Prepare Province For Choices
     * @return array
     */
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