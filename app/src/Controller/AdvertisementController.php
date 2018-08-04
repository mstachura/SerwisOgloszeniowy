<?php
/**
 * Advertisement controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Form\AdvertisementType;
use Service\FileUploader;
use Form\CommentType;


use Repository\AdvertisementRepository;
use Repository\UserRepository;
use Repository\CommentRepository;
use Repository\CategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

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
        $controller->match('/', [$this, 'indexAction'])
            ->bind('ads_index');
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
        $controller->match('/add', [$this, 'addAction'])
            ->method('POST|GET')
            ->bind('comment_add');
        $controller->get('/page/{page}', [$this, 'indexAction'])
            ->value('page', 1)
            ->bind('ads_index');
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
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return string Response
     */
    public function indexAction(Application $app, $page = 1)
    {
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $advertisements = $advertisementRepository->findAllPaginated($page);
//        dump($advertisements);
        return $app['twig']->render(
            'advertisement/index.html.twig',
            ['advertisements' => $advertisements]
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
        $categoryRepository = new CategoryRepository($app['db']);
        $ad = [];
        $form = $app['form.factory']->createBuilder(
            AdvertisementType::class,
            $ad,
            ['category_repository' => new CategoryRepository($app['db'])]
        )->getForm();
        $form->handleRequest($request);


//        if ($form->isSubmitted() && $form->isValid()) {
//            $ad = $form->getData();
//            dump($ad);
//        }

        if ($form->isSubmitted() && $form->isValid()) {
            $advertisementRepository = new AdvertisementRepository($app['db']);
            $ad = $form->getData();

//            $fileUploader = new FileUploader($app['config.photos_directory']);
//            $photo= [];
//            $fileName = $fileUploader->upload($ad['photo']);
      //      $photo['source'] = $fileName;

            $loggedUser['id'] = 1;
            $ad['user_id'] = $loggedUser['id'];
           // dump($result);
          $id = $advertisementRepository->save($ad);
      //      $photo['ad_id'] = $id;

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
                'form' => $form->createView(),
            ]
        );


    }

    public function editAction(Application $app, Request $request, $id){
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

        $form = $app['form.factory']->createBuilder(AdvertisementType::class,
            $ad,
            ['category_repository' => new CategoryRepository($app['db'])]
        )->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advertisementRepository->save($form->getData());

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

        $form = $app['form.factory']->createBuilder(FormType::class, $ad)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advertisementRepository->delete($form->getData());

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_deleted',
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
            ]
        );
    }


    public function viewAction(Application $app, $id, Request $request)
    {
        $advertisementRepository = new AdvertisementRepository($app['db']);
        $advertisement = $advertisementRepository->findOneById($id);

        if ($advertisement) {
            $userRepository = new UserRepository($app['db']);
//            $commentRepository = new CommentRepository($app['db']);
//            $comments = $commentRepository->findAllFromAdvertisement($id);


            $author = $userRepository->findOneById($advertisement['user_id']);

            $advertisement['author'] = $author['login'];
           //    dump($comments);

            // $categoryRepository = new CategoryRepository($app['db']);
//            $comment = [];
//            $form = $app['form.factory']->createBuilder(
//                CommentType::class,
//                $comment,
//                ['comment_repository' => new CommentRepository($app['db'])]
//            )->getForm();
//            $form->handleRequest($request);
//            dump($comment);



//            if ($form->isSubmitted() && $form->isValid()) {
//                $commentRepository = new CommentRepository($app['db']);
//                $comment = $form->getData();
//
//                $loggedUser['id'] = 1;
////                $comment['user_id'] = $loggedUser['id'];
////                $comment['ad_id'] = $id;
//
////               $commentRepository->save($comment);
//
//                $app['session']->getFlashBag()->add(
//                    'messages',
//                    [
//                        'type' => 'success',
//                        'message' => 'message.element_successfully_added',
//                    ]
//                );
//                return $app->redirect($app['url_generator']->generate('ads_view', ['id' => $id], 301));
//            }


            return $app['twig']->render(

                'advertisement/view.html.twig',
                [
                    'advertisement' => $advertisement,
//                    'comments' => $comments,
//                    'form' => $form->createView(),
                ]
            );
        } else {
            return $app->redirect($app['url_generator']->generate('home_index', 301));
        }
    }


}
