<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * IndexController
 */
class IndexController implements ControllerProviderInterface
{
    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function index(Application $app)
    {
        $vars = array('hello' => 'world');
        return $app['twig']->render('index.twig', $vars);
    }

    /**
     * @param \Silex\Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/", array($this, "index"))->bind('index');
        return $index;
    }
}
