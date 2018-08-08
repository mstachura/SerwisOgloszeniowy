<?php
/**
 * Hello controller.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */
namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HelloController.
 */
class HelloController implements ControllerProviderInterface
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
        $controller->get('/{name}', [$this, 'indexAction']);
        $controller->get('/{name}/add', [$this, 'addAction']);

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
        $name = 'olo';
        $baza_danych = [];
        $baza_danych['nazwa'] = 'serwis';
        $baza_danych['ilosc_tabel'] = 1;
        $baza_danych['ilosc_zdjec'] = 5;

        return $app['twig']->render('hello/menu.html.twig', ['name' => $name, 'bd' => $baza_danych]);
    }
    /**
     * add action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return string Response
     */
    public function addAction(Application $app)
    {
        $error = 'Nie mogę jeszcze dodać hello';


        return $app['twig']->render('hello/add.html.twig', ['error' => $error]);
    }
}
