<?php
/**
 * Home controller.
 *
 */
namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Repository\AdvertisementRepository;
use Repository\UserRepository;
use Repository\CategoryRepository;
use Form\SearchType;

/**
 * Class HomeController.
 */
class HomeController implements ControllerProviderInterface
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
        ->bind('home_index');

        $controller->get('/search', [$this, 'searchAction'])
            ->method('GET|POST')
            ->bind('home_search');

        return $controller;
    }

    /**
     * Index action
     * @param Application $app
     * @param Request $request
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function indexAction(Application $app, Request $request)
    {
      $advertisementRepository = new AdvertisementRepository($app['db']);
      $advertisements = $advertisementRepository-> findAll();

      $userRepository = new UserRepository($app['db']);
      $users = $userRepository-> findAllExtra();
      $loggedUser = $userRepository->getLoggedUser($app);

      $categoryRepository = new CategoryRepository($app['db']);
      $categories = $categoryRepository-> findAll();

        return $app['twig']->render(
            'home/index.html.twig',
            [
                'advertisements'=> $advertisements,
                'users' => $users,
                'categories' => $categories,
                'categoriesMenu' => $categoryRepository->findAll(),
                'loggedUser' => $loggedUser
            ]
        );
    }

    /**
     * Search action
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function searchAction(Application $app, Request $request){
        $categoryRepository = new CategoryRepository($app['db']);
        $userRepository = new UserRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

            $search = [];
            $search['category_search'] = '';



        $form = $app['form.factory']->createBuilder(
            SearchType::class,
            $search
        )->getForm();
        $form->handleRequest($request);


        $results = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();

            if($search['category_search'] == 'user'){
                if (!$search['phrase']) {
                    return $app->redirect($app['url_generator']->generate('user_index', 301));
                }
                return $app->redirect($app['url_generator']->generate('user_search', ['phrase' => $search['phrase']], 301));

            }elseif($search['category_search'] == 'advertisement'){
                if (!$search['phrase']) {
                    return $app->redirect($app['url_generator']->generate('ads_index', 301));
                }
                return $app->redirect($app['url_generator']->generate('ads_search', ['phrase' => $search['phrase']], 301));

            }
            else{
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'warning',
                        'message' => 'message.record_not_found',
                    ]
                );
                return $app->redirect($app['url_generator']->generate('home_index', 301));
            }
        }

//        dump($results);
        return $app['twig']->render(
            'home/search.html.twig', [
            'loggedUser' => $loggedUser,
//            'results' => $results,
            'categoriesMenu' => $categoryRepository->findAll(),
            'form' => $form->createView(),
//            'category_result' => $search['category_search']
        ]);
    }
}
