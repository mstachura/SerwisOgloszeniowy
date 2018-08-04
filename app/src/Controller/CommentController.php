<?php
/**
 * Comment controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Form\CommentType;



use Repository\CommentRepository;

use Repository\UserRepository;
use Repository\CategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class CommentController.
 */
class CommentController implements ControllerProviderInterface
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
        $controller->match('/{id}/edit', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('comment_edit');
        $controller->match('/{id}/delete', [$this, 'deleteAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9]\d*')
            ->bind('comment_delete');

        return $controller;
    }



    public function editAction(Application $app, Request $request, $id){
        $commentRepository = new CommentRepository($app['db']);
        $comment = $commentRepository->findOneById($id);

        if (!$comment) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('ads_index'));
        }

        $form = $app['form.factory']->createBuilder(CommentType::class, $comment)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentRepository->save($form->getData());

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
            'comment/edit.html.twig',
            [
                'comment' => $comment,
                'form' => $form->createView(),
            ]
        );
    }


    /**
     * Remove record.
     *
     * @param array $ad Tag
     *
     * @return boolean Result
     */
    public function deleteAction(Application $app, $id, Request $request)
    {
        $commentRepository = new CommentRepository($app['db']);
        $comment = $commentRepository->findOneById($id);

        if (!$comment) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'warning',
                    'message' => 'message.record_not_found',
                ]
            );

            return $app->redirect($app['url_generator']->generate('ads_index'));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $comment)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentRepository->delete($form->getData());

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
            'comment/delete.html.twig',
            [
                'comment' => $comment,
                'form' => $form->createView(),
            ]
        );
    }


}
