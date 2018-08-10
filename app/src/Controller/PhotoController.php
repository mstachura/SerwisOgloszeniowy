<?php
/**
 * Photo controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use Repository\PhotoRepository;
use Repository\CategoryRepository;
use Repository\AdvertisementRepository;
use Repository\DataRepository;
use Form\PhotoType;
use Repository\LocationRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


/**
 * Class PhotoController.
 */
class PhotoController implements ControllerProviderInterface
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
        $controller->match('/advertisement/{id}/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('photo_add');
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->assert('id', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('photo_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->assert('id', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('photo_delete');



        return $controller;
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

        $categoryRepository = new CategoryRepository($app['db']);
        $photoRepository = new PhotoRepository($app['db']);
        $userRepository = new UserRepository($app['db']);
        $loggedUser = $userRepository->getLoggedUser($app);

        $photo = [];
        $form = $app['form.factory']->createBuilder(
            PhotoType::class,
            $photo,
            [
                'photo_repository' => new PhotoRepository($app['db']),
                'location_repository' => new LocationRepository($app['db'])
            ]
        )->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $photoRepository = new PhotoRepository($app['db']);
            $photo = $form->getData();


//             dump($photo);
            $photoRepository->save($photo, $app);

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
            'photo/add.html.twig',
            [
                'photo' => $photo,
                'form' => $form->createView(),
                'loggedPhoto' => $loggedUser,
                'categoriesMenu' => $categoryRepository->findAll()
            ]
        );
//        return $app['twig']->render('photo/add.html.twig', ['error' => $error]);

    }

    public function editAction(Application $app, Request $request, $id){
        $photoRepository = new PhotoRepository($app['db']);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedPhoto = $photoRepository->getLoggedPhoto($app);
        $photoDataRepository = new DataRepository($app['db']);
        $photo = $photoRepository->findOneById($id); //tu ma być findOneByIdWithPhotoId
        $photo_data = $photoDataRepository ->findOneByPhotoId($id);
        $photo['firstname'] = $photo_data['firstname'];
        $photo['lastname'] = $photo_data['lastname'];
        $photo['phone_number'] = $photo_data['phone_number'];

//        dump($photo);
        if (!$photo) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',

                ]
            );

            return $app->redirect($app['url_generator']->generate('photo_index'));
        }
//        dump($photo);

        $form = $app['form.factory']->createBuilder(PhotoType::class, $photo)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoRepository->save($form->getData(), $app);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_edited',
                ]
            );

            return $app->redirect($app['url_generator']->generate('photo_view', ['id' => $id], 301));
        }

        return $app['twig']->render(
            'photo/edit.html.twig',
            [
                'photo' => $photo,
                'form' => $form->createView(),
                'loggedPhoto' => $loggedPhoto,
                'categoriesMenu' => $categoryRepository->findAll()
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
        $photoRepository = new PhotoRepository($app['db']);
        $photo = $photoRepository->findOneById($id);
        $categoryRepository = new CategoryRepository($app['db']);
        $loggedPhoto = $photoRepository->getLoggedPhoto($app);

        if (!$photo) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('photo_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $photo)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoRepository->delete($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
                ]
            );

            return $app->redirect(
                $app['url_generator']->generate('photo_index'),
                301
            );
        }

        return $app['twig']->render(
            'photo/delete.html.twig',
            [
                'photo' => $photo,
                'form' => $form->createView(),
                'loggedPhoto' => $loggedPhoto,
                'categoriesMenu' => $categoryRepository->findAll()
            ]
        );
    }


}
