<?php
/**
 * Category controller.
 *
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

        $controller->get('/{id}/page/{page}', [$this, 'viewAction'])
            ->value('page', 1)
            ->assert('id', '[1-9]\d*')
            ->bind('category_view');

        return $controller;
    }

    /**
     * View action
     * @param Application $app
     * @param $id
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function viewAction(Application $app, $id, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
//        $category = $categoryRepository->findAllPaginated($page);

        $loggedUser = $userRepository->getLoggedUser($app);
        $advertisementRepository = new AdvertisementRepository($app['db']);

        $advertisements = $advertisementRepository->findAllByCategoryPaginated($id, $page);
        $name_category = $categoryRepository->findOneById($id);
        $name_category = $name_category['name'];


        return $app['twig']->render(
            'category/view.html.twig',
            [
                'advertisements' => $advertisements,
                'name_category' => $name_category,
                'loggedUser' => $loggedUser,
                'categoriesMenu' => $categoryRepository->findAll(),
                'category_id' => $id
            ]
        );
    }
}
