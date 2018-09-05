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
use Form\CategoryType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

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
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('category_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('category_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('POST|GET')
            ->assert('id', '[1-9]\d*')
            ->bind('category_delete');

        return $controller;
    }

    public function addAction(Application $app, Request $request)
    {
        if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
            $categoryRepository = new CategoryRepository($app['db']);
            $userRepository = new UserRepository($app['db']);

            $loggedUser = $userRepository->getLoggedUser($app);


            $category = [];
            $form = $app['form.factory']->createBuilder(
                CategoryType::class,
                $category
            )->getForm();
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {
                $category = $form->getData();
                $categoryRepository->save($app, $category);

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
                'category/add.html.twig',
                [
                    'form' => $form->createView(),
                    'loggedUser' => $loggedUser,
                    'categoriesMenu' => $categoryRepository->findAll()
                ]
            );
        } else {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.you_are_not_admin',
                ]
            );
            return $app->redirect($app['url_generator']->generate('home_index', 301));
        }
    }

    public function editAction(Application $app, Request $request, $id)
    {
        $userRepository = new UserRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);


        $category = $categoryRepository->findOneById($id);


        if (!$category) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',

                ]
            );

            return $app->redirect($app['url_generator']->generate('home_index'));
        }

        if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {


            $form = $app['form.factory']->createBuilder(CategoryType::class, $category)->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $category = $form->getData();


                $categoryRepository->save($app, $category);

                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.element_successfully_edited',
                    ]
                );

                return $app->redirect($app['url_generator']->generate('home_index', 301));
            }


            return $app['twig']->render(
                'category/edit.html.twig',
                [
                    'category' => $category,
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
                    'message' => 'message.you_are_not_admin',
                ]
            );
            return $app->redirect($app['url_generator']->generate('home_index'));
        }
    }

    public function deleteAction(Application $app, $id, Request $request)
    {
        $userRepository = new UserRepository($app['db']);

        $categoryRepository = new CategoryRepository($app['db']);

        $loggedUser = $userRepository->getLoggedUser($app);

        if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
            $category = $categoryRepository->findOneById($id);
            if (!$category) {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'warning',
                        'message' => 'message.record_not_found',
                    ]
                );

                return $app->redirect($app['url_generator']->generate('home_index'));
            }


            $form = $app['form.factory']->
            createBuilder(FormType::class, $category)->add('id', HiddenType::class)->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $categoryRepository->delete($form->getData());

                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.element_successfully_deleted',
                    ]
                );

                return $app->redirect(
                    $app['url_generator']->generate('home_index'),
                    301
                );
            }

            return $app['twig']->render(
                'category/delete.html.twig',
                [
                    'category' => $category,
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
                    'message' => 'message.you_are_not_admin',
                ]
            );
            return $app->redirect($app['url_generator']->generate('home_index'));
        }
    }


    /**
     * View action
     * @param Application $app
     * @param $id
     * @param int $page
     * @return mixed
     */
    public function viewAction(Application $app, $id, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);

        $category = $categoryRepository->findOneById($id);

        if (!$category) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('home_index'));
        }

        $loggedUser = $userRepository->getLoggedUser($app);
        $advertisementRepository = new AdvertisementRepository($app['db']);

        $advertisements = $advertisementRepository->findAllByCategoryPaginated($id, $page);

        return $app['twig']->render(
            'category/view.html.twig',
            [
                'advertisements' => $advertisements,
                'category' => $category,
                'loggedUser' => $loggedUser,
                'categoriesMenu' => $categoryRepository->findAll(),
            ]
        );
    }
}
