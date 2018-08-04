<?php
/**
 * Routing and controllers.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Controller\HelloController;
use Controller\AdvertisementController;
use Controller\HomeController;
use Controller\UserController;
use Controller\CategoryController;
use Controller\AuthController;


$app->mount('/hello', new HelloController());
$app->mount('/advertisement', new AdvertisementController());
$app->mount('/', new HomeController());
$app->mount('/user', new UserController());
$app->mount('/category', new CategoryController());
$app->mount('/auth', new AuthController());




