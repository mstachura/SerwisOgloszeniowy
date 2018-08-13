<?php
///**
// * Locations data transformer.
// */
//namespace Form;
//
//use Repository\LocationRepository;
//use Symfony\Component\Form\DataTransformerInterface;
//
///**
// * Class LocationsDataTransformer.
// */
//class LocationsDataTransformer implements DataTransformerInterface
//{
//    /**
//     * Locations repository.
//     *
//     * @var LocationsRepository|null $locationsRepository
//     */
//    protected $locationsRepository = null;
//
//    /**
//     * LocationsDataTransformer constructor.
//     *
//     * @param LocationsRepository $locationsRepository Locations repository
//     */
//    public function __construct(LocationsRepository $locationsRepository)
//    {
//        $this->locationsRepository = $locationsRepository;
//    }
//
//    /**
//     * Transform array of locations Ids to string of names.
//     *
//     * @param array $locations Locations ids
//     *
//     * @return string Result
//     */
//    public function transform($locations)
//    {
//        if (null == $locations) {
//            return '';
//        }
//
//        return implode(',', $locations);
//    }
//
//    /**
//     * Transform string of location names into array of Locations Ids.
//     *
//     * @param string $string String of location names
//     *
//     * @return array Result
//     */
//    public function reverseTransform($string)
//    {
//        return explode(',', $string);
//    }
//}