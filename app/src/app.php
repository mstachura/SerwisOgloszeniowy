<?php
/**
 * Init application.
 */
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SecurityServiceProvider;

$app = new Application();


$app->register(new SessionServiceProvider());

$app->register(new AssetServiceProvider());
$app->register(
    new TwigServiceProvider(),
    [
        'twig.path' => dirname(dirname(__FILE__)).'/templates',
    ]
);

$app['config.photos_directory'] = __DIR__.'/../web/uploads/photos';
$app['config.download_photos_directory'] = 'uploads/photos';
//dump($app['config.download_photos_directory']);
//dump($app['config.download_files_directory'])
// ...
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...
    $twig->addGlobal('photos_directory', $app['config.photos_directory']);
    $twig->addGlobal('download_files_directory', $app['config.download_photos_directory']);

    return $twig;
});

$app->register(new LocaleServiceProvider());
$app->register(
    new TranslationServiceProvider(),
    [
        'locale' => 'pl',
        'locale_fallbacks' => array('en'),
    ]
);
$app->extend('translator', function ($translator, $app) {
    $translator->addResource('xliff', __DIR__.'/../translations/messages.en.xlf', 'en', 'messages');
    $translator->addResource('xliff', __DIR__.'/../translations/validators.en.xlf', 'en', 'validators');
    $translator->addResource('xliff', __DIR__.'/../translations/messages.pl.xlf', 'pl', 'messages');
    $translator->addResource('xliff', __DIR__.'/../translations/validators.pl.xlf', 'pl', 'validators');

    return $translator;
});

$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());

$app->register(
    new DoctrineServiceProvider(),
    [

        'db.options' => [
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'serwis_ogloszeniowy',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'driverOptions' => [
                1002 => 'SET NAMES utf8',
            ],
        ],
    ]
);



$app->register(
    new SecurityServiceProvider(),
    [
        'security.firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'main' => [
                'pattern' => '^.*$',
                'form' => [
                    'login_path' => 'auth_login',
                    'check_path' => 'auth_login_check',
                    'default_target_path' => 'home_index',
                    'username_parameter' => 'login_type[login]',
                    'password_parameter' => 'login_type[password]',
                ],
                'anonymous' => true,
                'logout' => [
                    'logout_path' => 'auth_logout',
                    'target_url' => 'home_index',
                ],
                'users' => function () use ($app) {
                    return new Provider\UserProvider($app['db']);
                },
            ],
        ],
        'security.access_rules' => [
            ['^/auth.+$', 'IS_AUTHENTICATED_ANONYMOUSLY'],

        ['^/registration$', 'IS_AUTHENTICATED_ANONYMOUSLY'],
        ['.+/delete$', 'IS_AUTHENTICATED_FULLY'],
        ['.+/edit$', 'IS_AUTHENTICATED_FULLY'],
        ['.+/add$', 'IS_AUTHENTICATED_FULLY'],
        ],
        'security.role_hierarchy' => [
            'ROLE_ADMIN' => ['ROLE_USER'],
        ],
    ]
);


return $app;

//C:\xampp\php\php.exe -S localhost:8888 -t app/web