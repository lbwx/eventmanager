<?php
namespace WxSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as ParentController;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;

use WxSecurityBundle\Form\RegistrationFormType;

use AppBundle\Entity\User;
use AppBundle\Entity\Company;
use AppBundle\Entity\GuestGroup;

/**
 * Description of RegistrationController
 *
 * @author Ľuboš Babocký
 * @copyright (c) 2016, Webaholix, s.r.o
 */
class RegistrationController extends ParentController {

	public function registerAction(Request $request) {
		/** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
		$userManager = $this->get('fos_user.user_manager');
		/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
		$dispatcher = $this->get('event_dispatcher');
		$user = new User();
		$company = new Company();
		$user->setCompany($company);
		$form = $this->createForm(RegistrationFormType::class, $user);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()){
			$event = new FormEvent($form, $request);
			$dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
			$defaultGroup = new GuestGroup();
			$defaultGroup->setName("Default");
			$defaultGroup->setDefault(1);
			$em = $this->getDoctrine()->getManager();
			$em->persist($user->getCompany());
			$defaultGroup->setCompany($user->getCompany());
			$em->persist($defaultGroup);
			$userManager->updateUser($user);
			$em->flush();
			return new \Symfony\Component\HttpFoundation\JsonResponse([
				'status' => TRUE,
				'result' => $this->render('Templates/Security/RegistrationConfirm.html.twig', ['user' => $user])->getContent()
			]);
		}
		return new \Symfony\Component\HttpFoundation\JsonResponse([
				'status' => FALSE,
				'error' => 'error',
				'result' => $this->render('Ajax/Forms/SecurityRegistrationForm.html.twig', ['form' => $form->createView()])->getContent()
			]);
	}

	public function loginAction(Request $request) {
		$authChecker = $this->container->get('security.authorization_checker');
		$router = $this->container->get('router');
		if($authChecker->isGranted('ROLE_USER')) {
			return new RedirectResponse($router->genrate('homepage'));
		} elseif ($authChecker->isGranted('ROLE_ADMIN')) {
			return new RedirectResponse($router->generate('event_manager'));
		}
	}
}
