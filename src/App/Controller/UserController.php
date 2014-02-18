<?php
/**
 * Auteur: Blaise de Carné - blaise@concretis.com
 */
namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function login(Application $app, Request $request)
    {
        // construction du formulaire
        $form = $app['form.factory']->createNamedBuilder('login', 'form')
          ->add('username', 'text')
          ->add('password', 'password')
          ->getForm();

        $form_error = $app['security.last_error']($request);
        if ($form_error != null) {
            $form->addError(new FormError("L'identifiant ou le mot de passe ne sont pas valides !"));
        }

        return $app['twig']->render('user/login.twig', array('form' => $form->createView()));
    }

    /**
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $index = $app['controllers_factory'];
        $index->match("/login", array($this, "login"))->bind('user.login');
        return $index;
    }
}
