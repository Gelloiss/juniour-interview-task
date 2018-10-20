<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Reg;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RegController extends Controller
{
	/**
     * @Route("/reg", name="registration")
     */
    public function newAction(Request $request)
    {
    	$error = false;
        $reg = new Reg();
        $reg->setLogin('');
        $reg->setPassword('');
        $reg->setEmail('');

        $form = $this->createFormBuilder($reg)
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
            ->add('email', EmailType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Еmail', 
                ),
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Зарегистрироваться',
                'attr' => array(
                    'class' => 'register', 
                )
            ))
            ->getForm();

        $form->handleRequest($request);

	    if ($form->isSubmitted() && $form->isValid()) {
	    	$userManager = $this->get('fos_user.user_manager');
	    	$email_exist = $userManager->findUserByEmail($form["email"]->getData());
	    	$login_exist = $userManager->findUserByUsername($form["login"]->getData());
	    	if ($email_exist || $login_exist) {
	    		$error = true;
	    	}
	    	else {
		        $user = $userManager->createUser();
			    $user->setUsername($form["login"]->getData());
			    $user->setEmail($form["email"]->getData());
			    $user->setPlainPassword($form["password"]->getData());
			    $user->setEnabled(1);
                $user->setRoles(array("ROLE_ADMIN"));
			    $userManager->updateUser($user);

                //login after reg
                $factory = $this->get('security.encoder_factory');
                $login = $userManager->findUserByUsername($form["login"]->getData());
                $encoder = $factory->getEncoder($login);
                $salt = $login->getSalt();
                $token = new UsernamePasswordToken($login, null, 'main', $login->getRoles());
                $this->get('security.token_storage')->setToken($token);
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $login_suc = true;
		    }
	    }
        if ((isset($login_suc)) && ($login_suc == true)) {
            return $this->redirect($this->generateUrl('adminEmployeeAll', array('page' => 1)));
        } 
        else {       
            return $this->render('reg/index.html.twig', array(
                'form' => $form->createView(),
                'error' => $error,
            ));
        }
    }
}