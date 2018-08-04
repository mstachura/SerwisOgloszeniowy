<?php
/**
 * Category controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;




use Repository\CategoryRepository;
use Repository\AdvertisementRepository;


/**
 * Class CategoryController.
 */
class CategoryController implements ControllerProviderInterface
{
    /**
     * Routing settings.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Silex\ControllerCollection Result
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])
            ->bind('category_index');
        $controller->get('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('category_view');
        return $controller;

    }

    public function viewAction(Application $app, $id){
        $categoryRepository = new CategoryRepository($app['db']);
        $advertisementRepository = new AdvertisementRepository($app['db']);

        $advertisements = $advertisementRepository->findAllByCategory($id);
        $name_category = $categoryRepository->findOneById($id);
        $name_category = $name_category['name'];


        return $app['twig']->render(
            'category/view.html.twig', [
            'advertisements' => $advertisements,
            'name_category' => $name_category
        ]);
    }

    public function indexAction(Application $app){
        $categoryRepository = new CategoryRepository($app['db']);
        $categories = $categoryRepository-> findAll();



        return $app['twig']->render(
            'category/index.html.twig', [
            'categories' => $categories,
        ]);
    }


}
