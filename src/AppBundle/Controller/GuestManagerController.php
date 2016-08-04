<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\GuestGroup;
use AppBundle\Entity\Guest;
use AppBundle\Form\GuestForm;
use AppBundle\Form\GuestGroupForm;

class GuestManagerController extends Controller {

	/**
	 * @Route("/guest-manager", name="guest_manager")
	 */
	public function indexAction(Request $request) {

		//[L:] Handle ajax guest form if exist		
		if($request->request->has('guest_form')): $this->handleGuestForm($request); return $this->redirectToRoute('guest_manager'); endif;
		//[L:] Company is main search param for everything!
		$searchParams['company'] = $this->getLoggedUser()->getCompany();
		$searchParams['deleted'] = 0;
		$session = new Session();
		if($session->has('selectedGroup')) {
			$searchParams['id'] = $session->get('selectedGroup');
		} else {
			$searchParams['default'] = 1;
		}
		/* @var $selectedGroup \AppBundle\Entity\GuestGroup */
		$selectedGroup = $this->getGuestGroupRepository()->findOneBy($searchParams);
		return $this->render('Templates/GuestManager/GuestManager.html.twig', [
					'currentGroup' => $selectedGroup,
					'guestGroups' => $this->getGuestGroupRepository()->findBy(['company'=>$selectedGroup->getCompany(), 'hidden'=>0, 'deleted'=>0])
				]);
    }

	private function handleGuestForm(Request $request) {
		//[L:] Get GuestId from session & GuestEntity from database (edit action)
		$session = new Session();
		if($session->has('guestId')) {
			$guest = $this->getGuestRepository()->find($session->get('guestId'));
			$session->remove('guestId');
		} else {	//[L:] Or create new guest (create action)
			$guest = new Guest();
			$guest->setCompany($this->getLoggedUser()->getCompany());
		}
		//[L:] Update Repository
		$form = $this->createForm(GuestForm::class, $guest);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()): $this->getGuestRepository($guest); endif;
	}

	/**
	 * @Route("/guest-manager/ajax-request/", name="guest_manager_ajax_requests")
	 */
	public function handleAjaxRequestAction(Request $request) {
		//[L:] Check if this action is called by ajax
		if(!$request->isXmlHttpRequest()): return $this->createErrorMessage('notAjax'); endif;
		//[L:] Check request
		if(!$request->request->has('action') || empty($request->request->get('action'))): return $this->createErrorMessage('missingAction'); endif;
		//[L:] If we have action, we can decide what to do:
		switch ($request->request->get('action')) {
			case 'loadGuestGroups': $response = $this->loadGuestGroups(); break;
			case 'addGuestGroup': $response = $this->addGuestGroup($request); break;
			case 'deleteGuestGroup': $response = $this->deleteGuestGroup(($request->request->has('groupId')? $request->request->get('groupId') : NULL)); break;
			case 'renameGuestGroup': $response =  $this->renameGuestGroup($request); break;
			case 'changeCurrentGroup': $response = $this->selectGuestGroup($request); break;
			case 'loadGuests': $response = $this->loadGuestList(); break;
			case 'createGuest': $response = $this->createGuest(); break;
			case 'editGuest': $response = $this->editGuest($request); break;
			case 'hideGuest': $response = $this->hideGuest($request); break;
			case 'deleteGuest': $response = $this->deleteGuest($request); break;
			default: return $this->createErrorMessage('unknownAction');
		}
		return new \Symfony\Component\HttpFoundation\JsonResponse($response);
	}
	private function loadGuestGroups() {
		//[L:] Search only for non deleted, non hidden and company owned groups
		$guestGroups = $this->getGuestGroupRepository()->findBy([
			'company' => $this->getLoggedUser()->getCompany(),
			'hidden' => 0,
			'deleted' => 0
		]);
		//[L:] next condition should never happens, there must be default group in each case
		if(!isset($guestGroups)): return $this->createErrorMessage('noGroups', FALSE); endif;

		$newGroup = new GuestGroup();
		$newGroupForm = $this->createForm(GuestGroupForm::class, $newGroup);

		return [
			'status' => TRUE,
			'callback' => 'loadGuestGroups',
			'result' => $this->render('Ajax/GuestManager/partialGroupTable.html.twig', ['guestGroups' => $guestGroups])->getContent(),
			'groupForm' => $this->render('Ajax/Forms/GuestGroupForm.html.twig', ['groupForm' => $newGroupForm->createView()])->getContent()
		];
	}
	private function addGuestGroup(Request $request) {
		$guestGroup = new GuestGroup();
		//[L:] Set group company
		$guestGroup->setCompany($this->getLoggedUser()->getCompany());
		//[L:] Check group name
		$form = $this->createForm(GuestGroupForm::class, $guestGroup);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			$this->getGuestGroupRepository($guestGroup);
		}
//		if(isset($newGuestGroupName) && !empty($newGuestGroupName)): $guestGroup->setName($newGuestGroupName); endif;
//		$this->getGuestGroupRepository($guestGroup);
		return $this->loadGuestGroups();
	}
	private function deleteGuestGroup($groupId) {
		//[L:] Check if groupId was sent
		if(!isset($groupId) || empty($groupId)): return $this->createErrorMessage('noGroupId', FALSE); endif;
		//[L:] Load group & test if it belongs to user's company
		$guestGroup = $this->getGuestGroupRepository()->find($groupId);
		/* @var $guestGroup \AppBundle\Entity\GuestGroup */
		if($guestGroup->getCompany() !== $this->getLoggedUser()->getCompany()): return $this->createErrorMessage('notYourGroup', FALSE); endif;
		$guestGroup->setDeleted(1);
		$this->getGuestGroupRepository($guestGroup);
		return $this->loadGuestGroups();
	}
	private function renameGuestGroup(Request $request) {
		//[L:] Check group id!
		if(!$request->request->has('groupId') || empty($request->request->get('groupId'))): $this->createErrorMessage('noGroupId'); endif;
		//[L:] Load group & test if it belongs to user's company
		/* @var $guestGroup \AppBundle\Entity\GuestGroup */
		$guestGroup = $this->getGuestGroupRepository()->findOneBy([
			'company' => $this->getLoggedUser()->getCompany(),
			'id'=>$request->request->get('groupId'),
			'default'=>FALSE
		]);

		if($request->request->has('newName')): $guestGroup->setName($request->request->get('newName')); endif;
		$this->getGuestGroupRepository($guestGroup);
		return $this->loadGuestGroups();
	}
	private function selectGuestGroup(Request $request) {
		if(!$request->request->has('groupId') || empty($request->request->get('groupId'))): $this->createErrorMessage('noGroupId'); endif;
		$selectedGroup = $this->getGuestGroupRepository()->findOneBy(['company'=> $this->getLoggedUser()->getCompany(), 'id' => $request->request->get('groupId')]);
		$session = new Session();
		$session->set('selectedGroup', $selectedGroup);
		return $this->loadGuestList();
	}
	private function loadGuestList() {
		$session = new Session();
		if($session->has('selectedGroup')) {
			$guestGroup = $session->get('selectedGroup');
		} else {
			$guestGroup = $this->getGuestGroupRepository()->findOneBy(['company' => $this->getLoggedUser()->getCompany(), 'default' =>1]);
		}
		$guests = $this->getGuestRepository()->findBy(['guestgroup' =>$guestGroup, 'deleted' => 0]);
		return ['status' => TRUE, 'callback' => 'loadGuestList', 'result' => $this->render('Ajax/GuestManager/partialGuestListTable.html.twig', ['guests' => $guests])->getContent()];
	}
	private function createGuest() {
		$guest = new Guest();
		$guestForm = $this->createForm(GuestForm::class, $guest);
		return [
			'status' => TRUE,
			'callback' => 'openGuestEditor',
			'result' => $this->render('Ajax/Forms/GuestManagerForm.html.twig', ['form' => $guestForm->createView()])->getContent()];
	}
	private function editGuest(Request $request) {
		if(!$request->request->has('guestId')): die('no guest id'); endif;
		$guest = $this->getGuestRepository()->findOneBy(['company' => $this->getLoggedUser()->getCompany(),'id' => $request->request->get('guestId')]);
		$session = new Session();
		$session->set('guestId', $guest->getId());
		$guestForm = $this->createForm(GuestForm::class, $guest);
		return [
			'status' => TRUE,
			'callback' => 'openGuestEditor',
			'result' => $this->render('Ajax/Forms/GuestManagerForm.html.twig', ['form' => $guestForm->createView()])->getContent()];
	}
	private function hideGuest(Request $request) {
		if(!$request->request->has('guestId')): die('no guest id'); endif;
		/* @var $guest \AppBundle\Entity\Guest */
		$guest = $this->getGuestRepository()->findOneBy(['company' => $this->getLoggedUser()->getCompany(),'id' => $request->request->get('guestId')]);
		$guest->setHidden(($guest->getHidden() == 1) ? 0 : 1);
		$this->getGuestRepository($guest);
		return $this->loadGuestList();
	}
	private function deleteGuest(Request $request) {
		if(!$request->request->has('guestId')): die('no guest id'); endif;
		/* @var $guest \AppBundle\Entity\Guest */
		$guest = $this->getGuestRepository()->findOneBy(['company' => $this->getLoggedUser()->getCompany(),'id' => $request->request->get('guestId')]);
		$guest->setDeleted(1);
		$this->getGuestRepository($guest);
		return $this->loadGuestList();
	}
	private function createErrorMessage($errorNo, $createJson = TRUE){
		switch ($errorNo) {
			case 'notAjax': $message = 'This is not an Ajax call!'; break;
			case 'missingAction': $message = 'Missing Action!'; break;
			case 'unknownAction': $message = 'This action is not supported!'; break;
			case 'noGroups': $message = 'Your company has no GuestGroups!'; break;
			case 'noGroupId': $message = 'No groupId sent!'; break;
			case 'notYourGroup': $message = 'This group doesn\'t belongs to your company!'; break;

			default : $message = 'Oops, something went wrong!'; break;
		}
		$response = ['status' => FALSE, 'error' => "$message Access Denied!"];
		if($createJson): return new \Symfony\Component\HttpFoundation\JsonResponse($response); endif;
		return $response;
	}

	/**
	 * @return \AppBundle\Repository\GuestRepository
	 */
	private function getGuestRepository(Guest $guest = NULL) {
		$em = $this->getDoctrine()->getManager();
		if(isset($guest)){
			$guest->setModified(new \DateTime);
			$em->persist($guest);
			$em->flush();
			return TRUE;
		} else {
			return $em->getRepository('AppBundle:Guest');
		}
	}

	/**
	 * @return \AppBundle\Repository\GuestGroupRepository
	 */
	private function getGuestGroupRepository(GuestGroup $guestGroup = NULL) {
		$em = $this->getDoctrine()->getManager();
		if(isset($guestGroup)){
			$guestGroup->setModified(new \DateTime);
			$em->persist($guestGroup);
			$em->flush();
			return TRUE;
		} else {
			return $em->getRepository('AppBundle:GuestGroup');
		}
	}

	/**
	 * @return \AppBundle\Entity\User
	 */
	private function getLoggedUser() {
		return $this->get('security.token_storage')->getToken()->getUser();
	}
}
