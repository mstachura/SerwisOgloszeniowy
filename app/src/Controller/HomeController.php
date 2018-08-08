<?php
/**
 * Home controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
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
     * Index action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return string Response
     */
    public function indexAction(Application $app, Request $request)
    {
      $advertisementRepository = new AdvertisementRepository($app['db']);
      $advertisements = $advertisementRepository-> findAll();

      $userRepository = new UserRepository($app['db']);
      $users = $userRepository-> findAll();
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


    public function searchAction(Application $app, Request $request){
        $categoryRepository = new CategoryRepository($app['db']);
        $userRepository = new UserRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        $phrase =[];

        $form = $app['form.factory']->createBuilder(
            SearchType::class,
            $phrase
        )->getForm();
        $form->handleRequest($request);

        $advertisements = [];


        if ($form->isSubmitted() && $form->isValid()) {
            $advertisementRepository = new AdvertisementRepository($app['db']);
            $result = $form->getData();

            $advertisements = $advertisementRepository -> findAllByPhraseOfName($result['search_name']);
        }


        return $app['twig']->render(
            'home/search.html.twig', [
            'loggedUser' => $loggedUser,
            'advertisements' => $advertisements,
            'categoriesMenu' => $categoryRepository->findAll(),
            'form' => $form->createView()
        ]);
    }
}
