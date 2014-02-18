<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace App;

use App\Entity\UserRepository;
use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Entea\Twig\Extension\AssetExtension;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\SwiftmailerServiceProvider;
use Yosymfony\Silex\ConfigServiceProvider\ConfigServiceProvider;

class Application extends SilexApplication
{
    public function __construct($debug = false)
    {
        parent::__construct();
        $app = $this;
        $this["debug"] = $debug;

        # config
        $app->register(new ConfigServiceProvider());
        $config = $app['configuration']->load(__DIR__ . "/../../config.yml");

        # services divers
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new ValidatorServiceProvider());

        # templating
        $this->register(
            new TwigServiceProvider(),
            array(
                'twig.path' => array(__DIR__ . '/../../templates'),
                'twig.options' => array('cache' => __DIR__ . '/../../cache'),
            )
        );
        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->addExtension(new AssetExtension($app));
            $twig->addExtension(new \Twig_Extensions_Extension_Text());
            $twig->addGlobal('user', $app['security']->getToken()->getUser());
            return $twig;
        }));

        # formulaire
        $this->register(new FormServiceProvider());

        # session
        $this->register(new SessionServiceProvider());

        # trans
        $this->register(new TranslationServiceProvider(), array("locale" => "fr"));

        # url generator
        $this->register(new UrlGeneratorServiceProvider());

        # mail
        $this->register(new SwiftmailerServiceProvider(), array(
            'swiftmailer.options' => $config->get('mail', array())
        ));

        # base de donnée
        $this->register(
            new DoctrineServiceProvider(),
            array(
                'db.options' => array(
                    'driver' => $config['db']['driver'],
                    'host' => $config['db']['host'],
                    'dbname' => $config['db']['dbname'],
                    'user' => $config['db']['user'],
                    'password' => $config['db']['password'],
                    'driverOptions' => array(1002 => "SET NAMES 'UTF8'")
                ),
            )
        );
        $app->register(new DoctrineOrmServiceProvider(), array(
                "orm.proxies_dir" => __DIR__ . '/../../cache/doctrine/proxy',
                "orm.em.options" => array(
                    "mappings" => array(
                        // Using actual filesystem paths
                        array(
                            "type" => 'annotation',
                            "namespace" => 'App\Entity',
                            "path" => __DIR__ . '/../App/Entity',
                            "use_simple_annotation_reader" => false
                        )
                    ),
                ),
            ));

        # sécurité
        $this->register(
            new SecurityServiceProvider(),
            array(
                'security.role_hierarchy' => array(
                    'ROLE_SUPER_ADMIN' => array('ROLE_ADMIN'),
                    'ROLE_ADMIN' => array('ROLE_USER_VIP')
                ),
                'security.firewalls' => array(
                    'secured' => array(
                        'pattern' => '^/',
                        'anonymous' => array(),
                        'form' => array(
                            'login_path' => "/user/login",
                            'check_path' => "/user/dologin",
                            "default_target_path" => "/",
                            "always_use_default_target_path" => true,
                            'username_parameter' => 'login[username]',
                            'password_parameter' => 'login[password]',
                            "csrf_parameter" => "login[_token]",
                            "failure_path" => "/user/login",
                        ),
                        'logout' => array(
                            'logout_path' => "/user/logout",
                            "target" => '/',
                            "invalidate_session" => true,
                            "delete_cookies" => array()
                        ),
                        'users' => $this->share(function () use ($app) {
                            return new UserRepository($app['orm.em'], $app['orm.em']->getClassMetadata('App\Entity\User'));
                        }),
                    ),
                ),
                'security.access_rules' => array(
                    array('^/user/login', 'IS_AUTHENTICATED_ANONYMOUSLY'),
                    array('^/user/password', 'IS_AUTHENTICATED_ANONYMOUSLY'),
                    array('^/admin', 'ROLE_ADMIN'),
                    array('^.*$', 'IS_AUTHENTICATED_FULLY'),
                )
            )
        );

        # gestion des erreurs
        $this->error(function (\Exception $e, $code) use ($app) {
            if ($app['debug']) {
                return;
            }
            $page = 404 == $code ? '404.twig' : '500.twig';
            return new Response($app['twig']->render($page, array('code' => $code)), $code);
        });

        # montage des controlleurs
        $this->setRoutes();
    }

    /*
     * setRoutes
     */
    function setRoutes() {
        # montage des controllers
        $this->mount("/", new \App\Controller\IndexController());
        $this->mount("/user", new \App\Controller\UserController());
    }
}
