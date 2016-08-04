<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\UserEditForm;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Entity\User;
use AppBundle\Entity\Company;
use AppBundle\Entity\GuestGroup;
use AppBundle\Form\UserForm;

/**
 * Description of SecurityController
 *
 * @author Ľuboš Babocký
 */
class SecurityController extends Controller {

	/**
	 * @Route("/ajax/registration/step1/", name="ajax_security_registration")
	 */
	public function ajaxRegistrationAction(Request $request) {
		$session = $request->getSession();
		if($session->has('userRegistrationId')) {
			$user = $this->getUserRepository()->findOneBy(['isActive'=>0, 'id'=>$session->get('userRegistrationId')]);
			$this->sendConfirmEmail($user);
			return $this->confirmAccount($user);
		}
		$user = new User();
		$company = new Company();
		$user->setCompany($company);
		$form = $this->createForm(UserForm::class, $user);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			$this->getCompanyRepository($user->getCompany());
			$this->createGuestGroup($user->getCompany());
			$user->setPassword($this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword()));
			$this->getUserRepository($user);
			$session->set('userRegistrationId', $user->getId());
			die('done');
		} else {
			return $this->render('Ajax/Forms/SecurityRegistrationForm.html.twig', ['form' => $form->createView()]);
		}
	}

	private function confirmAccount(User $user) {

		return $this->render('Templates/Security/RegistrationConfirm.html.twig', ['user'=>$user]);
	}

	private function sendConfirmEmail(User $user) {
		
		$confirmCode = 'ff';
		
		$body = "Your confirm code is: \n $confirmCode";
		$message = \Swift_Message::newInstance()->setSubject('test')->setFrom('eventmanager@webaholix.sk')->setTo('lb@webaholix.com')->setBody($body);
		$this->get('mailer')->send($message);
	}

	/**
	 * @Route("/ajax/registration/step-2/", name="ajax_security_register_confirm")
	 */
	public function ajaxRegistrationConfirmAction(Request $request) {
		$user = new User();die;
		$form = $this->createForm(UserForm::class, $user);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			$company = $user->getCompany();
			$company->setCreated(new \DateTime());
			$company->setModified(new \DateTime());
			$this->getCompanyRepository($company);
			$this->createGuestGroup($user->getCompany());
			$user->setPassword($this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword()));
			$this->getUserRepository($user);
			return new \Symfony\Component\HttpFoundation\JsonResponse([
				'status' => TRUE,
				'result' => $this->render('Templates/Security/RegistrationConfirm.html.twig', ['user' => $user])->getContent()
			]);
		} else {
			return new \Symfony\Component\HttpFoundation\JsonResponse([
				'status' => FALSE,
				'error' => 'error',
				'result' => $this->render('Ajax/Forms/SecurityRegistrationForm.html.twig', ['form' => $form->createView()])->getContent()
			]);
		}
	}

	/**
	 * @Route("edit-user", name="edit_user")
	 */
	public function editUserAction(Request $request) {
		$user = $this->getUserRepository()->find($this->getLoggedUser()->getId());
		$oldAvatar = $user->getAvatar();

		$avatarName = (!$user->getAvatar()) ? '__default.png' : $user->getAvatar();

		$user->setAvatar(new File($this->getParameter('avatars_directory').'/'.$avatarName));
		$form = $this->createForm(UserEditForm::class, $user);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			if($user->getAvatar()) {
				/** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
				$avatar = $user->getAvatar();
				$avatarName = md5(uniqid()).'.'.$avatar->guessExtension();
				$avatar->move(
					$this->getParameter('avatars_directory'),
					$avatarName
				);
				$user->setAvatar($avatarName);
			} else {
				$user->setAvatar($oldAvatar);
			}
			
			$this->getUserRepository($user);
		}

		return $this->render('Templates/Security/EditUser.html.twig', [
			'userForm' => $form->createView(),
			'user' => $user
		]);
	}
	
	
	/**
	 * @return \AppBundle\Entity\User
	 */
	private function getLoggedUser() {
		return $this->get('security.token_storage')->getToken()->getUser();
	}

	/**
	 * @return \AppBundle\Repository\UserRepository
	 */
	private function getUserRepository(User $user = NULL) {
		$em = $this->getDoctrine()->getManager();
		if(isset($user)){
			$em->persist($user);
			$em->flush();
			return TRUE;
		} else {
			return $em->getRepository('AppBundle:User');
		}
	}

	/**
	 * @return \AppBundle\Repository\CompanyRepository
	 */
	private function getCompanyRepository(Company $company = NULL) {
		$em = $this->getDoctrine()->getManager();
		if(isset($company)){
			$company->setModified(new \DateTime);
			$em->persist($company);
			$em->flush();
			return TRUE;
		} else {
			return $em->getRepository('AppBundle:Company');
		}
	}

	/**
	 * @param Company $company
	 */
	private function createGuestGroup(Company $company) {
		$guestGroup = new GuestGroup();
		$guestGroup->setName('Default');
		$guestGroup->setDefault(1);
		$guestGroup->setCompany($company);
		$em = $this->getDoctrine()->getManager();
		$em->persist($guestGroup);
		$em->flush();
	}
}