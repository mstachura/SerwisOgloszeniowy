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
use Repository\UserRepository;
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
        $userRepository = new UserRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);
        $advertisementRepository = new AdvertisementRepository($app['db']);

        $advertisements = $advertisementRepository->findAllByCategory($id);
        $name_category = $categoryRepository->findOneById($id);
        $name_category = $name_category['name'];


        return $app['twig']->render(
            'category/view.html.twig', [
            'advertisements' => $advertisements,
            'name_category' => $name_category,
            'loggedUser' => $loggedUser,
            'categoriesMenu' => $categoryRepository->findAll()
        ]);
    }

    public function indexAction(Application $app){
        $userRepository = new UserRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);
        $categories = $categoryRepository-> findAll();



        return $app['twig']->render(
            'category/menu.html.twig',
            [
                'categories' => $categories,
                'loggedUser' => $loggedUser,
                'categoriesMenu' => $categoryRepository->findAll()
            ]
        );
    }


}
