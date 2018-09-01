<?php
/**
 * User controller.
 *
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Repository\UserRepository;
use Repository\CategoryRepository;
use Repository\AdvertisementRepository;
use Repository\DataRepository;
use Form\UserType;
use Repository\LocationRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class UserController.
 */
class UserController implements ControllerProviderInterface
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
//        $controller->match('/', [$this, 'indexAction'])
//            ->bind('user_index');
        $controller->match('/{id}/page/{page}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->method('POST|GET')
            ->value('page', 1)
            ->bind('user_view');
        $controller->match('/registration', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('user_registration');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->assert('id', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('user_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->assert('id', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('user_delete');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('user_index');
//        $controller->get('/search/{phrase}', [$this, 'searchAction'])
//            ->value('page', 1)
//            ->bind('user_search');
        $controller->get('/search/{phrase}/page/{page}', [$this, 'searchActionPaginated'])
            ->value('page', 1)
            ->bind('user_search');


        return $controller;
    }

    /**
     * Index action
     * @param Application $app
     * @param int $page
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function indexAction(Application $app, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);
        $users = $userRepository->findAllPaginated($page);

        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        return $app['twig']->render(
            'user/index.html.twig',
            [
                'users' => $users,
                'loggedUser' => $loggedUser,
                'categoriesMenu' => $categoryRepository->findAll()
            ]
        );
    }

    /**
     * Add action
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addAction(Application $app, Request $request)
    {

        $categoryRepository = new CategoryRepository($app['db']);
        $userRepository = new UserRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        $user = [];
        $form = $app['form.factory']->createBuilder(
            UserType::class,
            $user,
            [
                'user_repository' => new UserRepository($app['db']) //unique login
            ]
        )->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = new UserRepository($app['db']);
            $user = $form->getData();

            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($user['password'], '');


            $data['user_id'] = $loggedUser['id'];


            $userRepository->save($app, $user);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );

            return $app->redirect($app['url_generator']->generate('home_index', 301));
        }


        return $app['twig']->render(
            'user/add.html.twig',
            [
                'user' => $user,
                'form' => $form->createView(),
                'loggedUser' => $loggedUser,
                'categoriesMenu' => $categoryRepository->findAll()
            ]
        );
    }

    /**
     * Edit action
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function editAction(Application $app, Request $request, $id)
    {
        $userRepository = new UserRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        $userDataRepository = new DataRepository($app['db']);
        $user = $userRepository->findOneById($id); //tu ma być findOneByIdWithUserId
        $user_data = $userDataRepository->findOneByUserId($id);
        $user['firstname'] = $user_data['firstname'];
        $user['lastname'] = $user_data['lastname'];
        $user['phone_number'] = $user_data['phone_number'];

        //załączanie lokalizacji
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $ad = $advertisementRepository->findOneById($id);

        $locationRepository = new LocationRepository($app['db']);
        $location = $locationRepository->findOneById($ad['location_id']);

        $user['location_name'] = $location['name'];

        if (!$user) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',

                ]
            );

            return $app->redirect($app['url_generator']->generate('user_index'));
        }

        if ($loggedUser['id'] == $id or $app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
            $form = $app['form.factory']->createBuilder(UserType::class, $user)->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $userRepository->save($app, $form->getData());

                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.element_successfully_edited',
                    ]
                );

                return $app->redirect($app['url_generator']->generate('user_view', ['id' => $id], 301));
            }

            return $app['twig']->render(
                'user/edit.html.twig',
                [
                    'user' => $user,
                    'form' => $form->createView(),
                    'loggedUser' => $loggedUser,
                    'categoriesMenu' => $categoryRepository->findAll()
                ]
            );
        } else {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.it_is_not_your_profile',
                ]
            );
            return $app->redirect($app['url_generator']->generate('home_index'));
        }
    }

    /**
     * Delete action
     * @param Application $app
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function deleteAction(Application $app, $id, Request $request)
    {
        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneById($id);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        if ($loggedUser['id'] == $id or $app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
            if (!$user) {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'warning',
                        'message' => 'message.record_not_found',
                    ]
                );

                return $app->redirect($app['url_generator']->generate('user_index'));
            }

            if ($loggedUser['id'] == $id or $app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
                $form = $app['form.factory']->
                createBuilder(FormType::class, $user)->add('id', HiddenType::class)->getForm();
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $userRepository->delete($form->getData());

                    $app['session']->getFlashBag()->add(
                        'messages',
                        [
                            'type' => 'success',
                            'message' => 'message.element_successfully_deleted',
                        ]
                    );

                    return $app->redirect(
                        $app['url_generator']->generate('user_index'),
                        301
                    );
                }

                return $app['twig']->render(
                    'user/delete.html.twig',
                    [
                        'user' => $user,
                        'form' => $form->createView(),
                        'loggedUser' => $loggedUser,
                        'categoriesMenu' => $categoryRepository->findAll()
                    ]
                );
            } else {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'warning',
                        'message' => 'message.it_is_not_your_profile',
                    ]
                );
                return $app->redirect($app['url_generator']->generate('home_index'));
            }
        } else {
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

    /**
     * View action
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function viewAction(Application $app, $id, $page = 1)
    {
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $advertisements = $advertisementRepository->findAllByUserPaginated($id, $page);

        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneByIdWithUserData($id);




        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);




        if ($user) {
            $locationRepository = new LocationRepository($app['db']);
            $location = $locationRepository->findOneById($user['location_id']);

            $user['location_name'] = $location['name'];

            return $app['twig']->render(

                'user/view.html.twig',
                [
                    'advertisements' => $advertisements,
                    'user' => $user,
                    'loggedUser' => $loggedUser,
                    'categoriesMenu' => $categoryRepository->findAll()
                ]
            );
        } else {
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

    /**
     * Search action Paginated
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function searchActionPaginated(Application $app, Request $request, $phrase, $page = 1)
    {
        $categoryRepository = new CategoryRepository($app['db']);
        $userRepository = new UserRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);
        $users = $userRepository->findByPhrasePaginated($phrase, $page);

        return $app['twig']->render(
            'user/search.html.twig',
            [
                'loggedUser' => $loggedUser,
                'users' => $users,
                'categoriesMenu' => $categoryRepository->findAll(),
                'phrase' => $phrase
            ]
        );
    }
}
