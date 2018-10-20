<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Login;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginController extends Controller
{
	/**
     * @Route("/login", name="login")
     */
    public function newAction(Request $request)
    {
        $login = new Login();
        $login->setLogin('');
        $login->setPassword('');

        $form = $this->createFormBuilder($login)
            ->add('login', TextType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Логин', 
                ),
            ))
            ->add('password', PasswordType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Пароль', 
                ),
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Войти',
                'attr' => array(
                    'class' => 'register', 
                ),
            ))
            ->getForm();

        $form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve the security encoder of symfony
            $factory = $this->get('security.encoder_factory');
	    	$userManager = $this->get('fos_user.user_manager');
	    	$user = $userManager->findUserByUsername($form["login"]->getData());
	    	if (!$user) {
	    		$error = true;
	    	}
	    	else {
		        $encoder = $factory->getEncoder($user);
                $salt = $user->getSalt();
                if(!$encoder->isPasswordValid($user->getPassword(), $form['password']->getData(), $salt)) {
                    $error = true;
                }
                else {
                    $error = false;
                }
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->get('security.token_storage')->setToken($token);
                // Fire the login event manually
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
		    }
	    }
        if (!isset($error) || $error) {
            if (!isset($error)) {
                $error = false;
            }
            return $this->render('login/index.html.twig', array(
                'form' => $form->createView(),
                'error' => $error,
            ));
        }
        else {
            return $this->redirect($this->generateUrl('adminEmployeeAll', array('page' => 1)));
        }
    }

    /**
     * @Route("/", name="redirectToLogin")
     */
    public function redirectToLogin()
    {
        return $this->redirect($this->generateUrl('login'));
    }
}