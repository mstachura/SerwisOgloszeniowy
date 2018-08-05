<?php
/**
 * User controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use Repository\UserRepository;
use Repository\AdvertisementRepository;
use Repository\DataRepository;
use Form\UserType;
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
        $controller->match('/', [$this, 'indexAction'])
            ->bind('user_index');
        $controller->match('/{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9]\d*')
            ->method('POST|GET')
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


        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return string Response
     */
    public function indexAction(Application $app)
    {
        $userRepository = new UserRepository($app['db']);

        return $app['twig']->render(
            'user/index.html.twig',
            ['users' => $userRepository->findAll()]
        );
    }

    /**
     * add action.
     *
     * @param \Silex\Application $app Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return string Response
     */
    public function addAction(Application $app, Request $request)
    {

        $user = [];
        $form = $app['form.factory']->createBuilder(
            UserType::class,
            $user,
            ['user_repository' => new UserRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = new UserRepository($app['db']);
            $user = $form->getData();


//             dump($user);
            $userRepository->save($user);

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
            ]
        );
//        return $app['twig']->render('user/add.html.twig', ['error' => $error]);

    }

    public function editAction(Application $app, Request $request, $id){
        $userRepository = new UserRepository($app['db']);
        $userDataRepository = new DataRepository($app['db']);
        $user = $userRepository->findOneById($id); //tu ma być findOneByIdWithUserId
        $user_data = $userDataRepository ->findOneByUserId($id);
        $user['firstname'] = $user_data['firstname'];
        $user['lastname'] = $user_data['lastname'];
        $user['phone_number'] = $user_data['phone_number'];

//        dump($user);
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
//        dump($user);

        $form = $app['form.factory']->createBuilder(UserType::class, $user)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($form->getData(), $app);

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
            ]
        );
    }

    /**
     * Remove record.
     *
     * @param array $ad Tag
     *
     *
     */
    public function deleteAction(Application $app, $id, Request $request)
    {
        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneById($id);

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

        $form = $app['form.factory']->createBuilder(FormType::class, $user)->add('id', HiddenType::class)->getForm();
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
            ]
        );
    }

    public function viewAction(Application $app, $id)
    {
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $advertisements = $advertisementRepository->findAllByUser($id);

        $userRepository = new UserRepository($app['db']);
        $user = $userRepository->findOneById($id);

        if ($user) {

            $userDataRepository = new DataRepository($app['db']);
            $userData = $userDataRepository->findOneByUserId($user['id']);


            return $app['twig']->render(

                'user/view.html.twig',
                [
                    'advertisements' => $advertisements,
                    'user' => $user,
                    'userData' => $userData
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
}