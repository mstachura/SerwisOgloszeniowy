<?php
/**
 * Advertisement controller.
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Form\AdvertisementType;
use Service\FileUploader;
use Repository\LocationRepository;
use Repository\TypeRepository;
use Repository\AdvertisementRepository;
use Repository\UserRepository;
use Repository\CategoryRepository;
use Repository\PhotoRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AdvertisementController.
 */
class AdvertisementController implements ControllerProviderInterface
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
//            ->bind('ads_index');
        $controller->get('/{name}/add', [$this, 'addAction']);
        $controller->match('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->bind('ads_view');
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('ads_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('ads_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('ads_delete');
//        $controller->match('/add', [$this, 'addAction'])
//            ->method('POST|GET')
//            ->bind('comment_add');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('ads_index');
//        $controller->get('/search/{phrase}', [$this, 'searchAction'])
//            ->value('page', 1)
//            ->bind('ads_search');
        $controller->get('/search/{phrase}/page/{page}', [$this, 'searchActionPaginated'])
            ->value('page', 1)
            ->bind('ads_search');

//        $controller->match('/{id}/edit', [$this, 'editAction'])
//            ->method('GET|POST')
//            ->assert('id', '[1-9]\d*')
//            ->bind('comment_edit');
//        $controller->match('/{id}/comment/delete', [$this, 'deleteCommentAction'])
//            ->method('GET|POST')
//            ->assert('id', '[1-9]\d*')
//            ->bind('comment_delete');


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
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $advertisements = $advertisementRepository->findAllPaginated($page);
        $categoryRepository = new CategoryRepository($app['db']);
        $userRepository = new UserRepository($app['db']);
//        $users = $userRepository->findAll();
        $loggedUser = $userRepository->getLoggedUser($app);





        return $app['twig']->render(
            'advertisement/index.html.twig',
            [
                'advertisements' => $advertisements,
                'categoriesMenu' => $categoryRepository->findAll(),
                'loggedUser' => $loggedUser
            ]
        );
    }


    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addAction(Application $app, Request $request)
    {
        $categoryRepository = new CategoryRepository($app['db']);
        $userRepository = new UserRepository($app['db']);

        $loggedUser = $userRepository->getLoggedUser($app);

        $ad = [];

        $locationRepository = new LocationRepository($app['db']);
        $location = $locationRepository->findOneById($loggedUser['location_id']);

        $ad['location_name'] = $location['name'];

        $form = $app['form.factory']->createBuilder(
            AdvertisementType::class,
            $ad,
            [
                'category_repository' => new CategoryRepository($app['db']), //lista rozwijana
                'type_repository' => new TypeRepository($app['db'])
            ]
        )->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) { //czy przesłano formularz? czy jest zwalidowany?
            $advertisementRepository = new AdvertisementRepository($app['db']);
            $data = $form->getData(); //dane advertisement i photo

            if ($data['photo']) {
                $fileUploader = new FileUploader($app['config.photos_directory']);

                $fileName = $fileUploader->upload($data['photo']);
                $data['source'] = $fileName;
            }


            $data['user_id'] = $loggedUser['id']; //autorem ogłoszenia jest zalogowany użytkownik


            $id = $advertisementRepository->save($app, $data);


            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
                ]
            );
            return $app->redirect($app['url_generator']->generate('ads_view', ['id' => $id], 301));
        }


        return $app['twig']->render(
            'advertisement/add.html.twig',
            [
                'ad' => $ad,
                'loggedUser' => $loggedUser,
                'categoriesMenu' => $categoryRepository->findAll(),
                'form' => $form->createView(),
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
//        $loggedUser = [];

        $categoryRepository = new CategoryRepository($app['db']);
        $userRepository = new UserRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $ad = $advertisementRepository->findOneById($id);

        if (!$ad) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('ads_index'));
        }

        $photoRepository = new PhotoRepository($app['db']);
        $photo = $photoRepository->findOneByAdvertisementId($id);

        if ($ad['user_id'] == $loggedUser['id'] or $app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
            if ($photo) {
                $ad['photo_title'] = $photo['name'];
            } else {
                $photo['source'] = '';
            }

            $locationRepository = new LocationRepository($app['db']);
            $location = $locationRepository->findOneById($ad['location_id']);
            $ad['location_name'] = $location['name'];

            $photoRepository = new PhotoRepository($app['db']);
            $photo = $photoRepository->findOneByAdvertisementId($id);

            $form = $app['form.factory']->createBuilder(
                AdvertisementType::class,
                $ad,
                [
                    'category_repository' => new CategoryRepository($app['db']),
                    'type_repository' => new TypeRepository($app['db']),
                ]
            )->getForm();

            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData(); //dane advertisement i photo

                if ($data['photo']) {
                    $fileUploader = new FileUploader($app['config.photos_directory']);

                    $fileName = $fileUploader->upload($data['photo']);
                    $data['source'] = $fileName;
                }

                $advertisementRepository->save($app, $data);

                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.element_successfully_edited',
                    ]
                );

                return $app->redirect($app['url_generator']->generate('ads_view', ['id' => $id], 301));
            }


            return $app['twig']->render(
                'advertisement/edit.html.twig',
                [
                    'ad' => $ad,
                    'form' => $form->createView(),
                    'photo' => $photo,
                    'loggedUser' => $loggedUser,
                    'categoriesMenu' => $categoryRepository->findAll()
                ]
            );
        } else {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.its_not_your_ad',
                ]
            );
            return $app->redirect($app['url_generator']->generate('ads_view', ['id' => $id], 301));
        }
    }

    /**
     * Delete action
     * @param Application $app
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function deleteAction(Application $app, $id, Request $request)
    {
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $ad = $advertisementRepository->findOneById($id);
        $userRepository = new UserRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        if (!$ad) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('ads_index'));
        }

        if ($ad['user_id'] == $loggedUser['id'] or $app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
            $form = $app['form.factory']->createBuilder(FormType::class, $ad)->add('id', HiddenType::class)->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $advertisementRepository->delete($form->getData());

                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.element_successfully_deleted',
                        'loggedUser' => $loggedUser,
                        'categoriesMenu' => $categoryRepository->findAll()
                    ]
                );

                return $app->redirect(
                    $app['url_generator']->generate('ads_index'),
                    301
                );
            }

            return $app['twig']->render(
                'advertisement/delete.html.twig',
                [
                    'ad' => $ad,
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
                    'message' => 'message.its_not_your_ad',
                ]
            );
            return $app->redirect($app['url_generator']->generate('ads_view', ['id' => $id], 301));
        }
    }

    /**
     * View action
     * @param Application $app
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function viewAction(Application $app, $id, Request $request)
    {
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $advertisement = $advertisementRepository->findOneByIdExtra($id);
        $userRepository = new UserRepository($app['db']);
        $photoRepository = new PhotoRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        $photo = $photoRepository->findOneByAdvertisementId($id);
        if ($advertisement) {
            return $app['twig']->render(

                'advertisement/view.html.twig',
                [
                    'photo' => $photo,
                    'advertisement' => $advertisement,
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
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $advertisements = $advertisementRepository->findByPhrasePaginated($phrase, $page);


        return $app['twig']->render(
            'advertisement/search.html.twig',
            [
                'loggedUser' => $loggedUser,
                'advertisements' => $advertisements,
                'categoriesMenu' => $categoryRepository->findAll(),
                'phrase' => $phrase
            ]
        );
    }
}
