<?php
/**
 * Photo type.
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
 * Class PhotoType.
 *
 * @package Form
 */
class PhotoType extends AbstractType
{
    /**
     * Build Form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($options['data']) || !isset($options['data']['id'])) {
            $builder->add(
                'source',
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
            'name',
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
                        ['groups' => ['photo-default']]
                    ),
                ],
            ]
        );
    }
}