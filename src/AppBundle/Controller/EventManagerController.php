<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Session\Session;

use AppBundle\Entity\Event;
use AppBundle\Entity\Invitation;
use AppBundle\Form\EventForm;
use AppBundle\Entity\GuestGroup;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;


class EventManagerController extends Controller {

	/**
	 * @Route("/event-manager", name="event_manager")
	 */
	public function indexAction(Request $request) {
		if($request->request->has('event_form')) {
			$this->handleEventForm($request);
			return $this->redirectToRoute('event_manager');
		}
		$session = new Session();
		if($request->query->get('list-mode')) {
			$session->set('current-list-mode', $request->query->get('list-mode'));
		}
        return $this->render('Templates/EventManager/EventManager.html.twig', [
			'events' => $this->loadEventList($session->get('current-list-mode')),
			'currentMode' => $session->get('current-list-mode')
		]);
    }

	private function handleEventForm(Request $request) {
		$session = new Session();
		if($session->has('editted-event-id')) {
			$event = $this->getEventRepository()->find($session->get('editted-event-id'));
		} else {
			$event = new Event();
			$event->setCompany($this->getLoggedUser()->getCompany());
			$event->setCreated(new \DateTime());
			$event->setHidden(0);
			$event->setDeleted(0);
		}
		$session->remove('editted-event-id');
		$form = $this->createForm(EventForm::class, $event);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()){
			$this->getEventRepository($event);
		}
	}

	/**
	 * @Route("/ajax/hideEvent", name="ajax_event_hide")
	 */
	public function hideAction(Request $request) {
		if($request->isXmlHttpRequest()) {
			/*
			$em = $this->getDoctrine()->getManager();
			$eventManagerRepository = $em->getRepository('AppBundle:Event');
			$event = $eventManagerRepository->find($request->request->get('eventId'));
			*/
			$event = $this->getEventRepository()->find($request->request->get('eventId'));
			$hidden = ($event->getHidden() == 0)? 1 : 0;
			$event->setHidden($hidden);
			$this->getEventRepository($event);
			return new \Symfony\Component\HttpFoundation\JsonResponse(array(
				'status' => 'Ok',
				'id'=> $event->getId(),
				'modified' => date("H:i:s - Y-m-d", $event->getModified()->getTimestamp())
			));
		}
		die('This is not an ajax call!!!');
	}

	/**
	 * @Route("/ajax/editEvent", name="ajax_event_edit")
	 */
	public function ajaxEditAction(Request $request) {
		if(!$request->isXmlHttpRequest()): die('Access denied'); endif;
		if(!$request->request->has('eventId') || empty($request->request->get('eventId'))): die('Access denied'); endif;
		$event = $this->getEventRepository()->find($request->request->get('eventId'));
		if($this->getLoggedUser()->getCompany()->getId() !== $event->getCompany()->getId()): die('Access denied'); endif;
		$form = $this->createForm(EventForm::class, $event);
		$session = new Session();
		$session->set('editted-event-id', $request->request->get('eventId'));
		return $this->render('Ajax/Forms/EventManagerForm.html.twig', [
			'form' => $form->createView(),
			'event' => $event,
			'submitLabel' => 'Manager.Event.Submit.Update'
		]);
	}

	/**
	 * @Route("/ajax/createEvent", name="ajax_event_create")
	 */
	public function ajaxCreateAction(Request $request) {
		if(!$request->isXmlHttpRequest()): die('Access denied'); endif;
		$event = $this->getNewEvent();
		$event->setStart(new \DateTime('tomorrow'));
		$event->setEnd(new \DateTime('tomorrow'));
		$form = $this->createForm(EventForm::class, $event);
		return $this->render('Ajax/Forms/EventManagerForm.html.twig', [
			'form' => $form->createView(),
			'submitLabel' => 'Manager.Event.Submit.Create'
		]);
	}

	/**
	 * 
	 * @return \AppBundle\Entity\Event
	 */
	private function getNewEvent() {
		$event = new Event();
		$event->setStart(new \DateTime());
		$event->setEnd(new \DateTime());
		$event->setCreated(new \DateTime());
		$event->setHidden(0);
		$event->setDeleted(0);
		return $event;
	}

	/**
	 * @Route("/event-manager/delete", name="event_manager_delete")
	 */
	public function deleteEventAction(Request $request) {
		$event = $this->getEventRepository()->find($request->query->get('event-id'));
		$event->setDeleted(1);
		$this->getEventRepository($event);
		$this->addFlash('notice', 'Event '.$event->getName().' was deleted!');
		return $this->redirectToRoute('event_manager');
	}

	/**
	 * @Route("/event-manager/restore", name="event_manager_restore")
	 */
	public function restoreEventAction(Request $request) {
		$event = $this->getEventRepository()->find($request->query->get('event-id'));
		$event->setDeleted(0);
		$this->getEventRepository($event);
		$this->addFlash('notice', 'Event '.$event->getName().' was restored!');
		return $this->redirectToRoute('event_manager');
	}

	private function loadEventList($mode = 'future') {
		switch ($mode) {
			case 'future':	return $this->getEventRepository()->findActualEvents($this->getLoggedUser()->getCompany()->getId());
			case 'past':	return $this->getEventRepository()->findPastEvents($this->getLoggedUser()->getCompany()->getId());
			case 'trash':	return $this->getEventRepository()->findDeletedEvents($this->getLoggedUser()->getCompany()->getId());
			default: return $this->getEventRepository()->findActualEvents($this->getLoggedUser()->getCompany()->getId());
		}
	}

	/**
	 * @Route("/ajax/events/loadContent/", name="ajax_events_load_content")
	 */
	public function ajaxLoadContent(Request $request) {
		$company = $this->getLoggedUser()->getCompany();
		$event = $this->getEventRepository()->findOneBy([
			'company' => $company,
			'id' => $request->get('eventId')
		]);
		if(!isset($event)): die('no event found'); endif;
		
		$result = $this->render(
				'Ajax/EventManager/LoadGuestGroups.html.twig',
				[	'event' => $event,
					'guestGroups' => $this->getGuestGroupRepository()->findBy(['company' => $company, 'hidden' => 0, 'deleted' => 0]),
					'guests' => $this->getGuestRepository()->findBy(['company'=>$company], ['lastName'=>'ASC']),
					'invitations' => $this->getInvitationRepository()->findBy(['event' => $event])
				]
			)->getContent();
		return new \Symfony\Component\HttpFoundation\JsonResponse($result);
	}
//***********

	/**
	 * @Route("/events/test/", name="events_test")
	 */
	public function testAction(Request $request) {
		if(!$request->request->has('eventId')): die('No event id!'); endif;
		/* @var $company \AppBundle\Entity\Company */
		$company = $this->getLoggedUser()->getCompany();
		/* @var $event \AppBundle\Entity\Event */
		$event = $this->getEventRepository()->findOneBy([
			'company' => $company,
			'id' => $request->request->get('eventId')
		]);
		if(!isset($event)): die('Event not found!'); endif;
		foreach ($request->get('guest') as $guestId => $guestData) {
			/* @var $guest \AppBundle\Entity\Guest */
			$guest = $this->getGuestRepository()->findOneBy([
				'company' => $company,
				'id' => $guestId,
				'hidden' => 0,
				'deleted' => 0
			]);
			if(!isset($guest)): continue; endif;
			if($guestData['invitation']==='exist') {
				/* @var $invitation \AppBundle\Entity\Invitation */
				$invitation = $this->getInvitationRepository()->findOneBy([
					'event' => $event,
					'guest' => $guest
				]);
				$invitation->setPlus($guestData['plus']);
				$invitation->setVip((isset($guestData['vip']) && $guestData['vip'] === 'true')?1:0);
				$invitation->setDeleted((isset($guestData['invite']) && $guestData['invite']==='true')?0:1);
				$this->getInvitationRepository($invitation);
			} elseif (isset($guestData['invite']) && $guestData['invite']==='true') {
				$invitation = new Invitation();
				$invitation->setEvent($event);
				$invitation->setGuest($guest);
				$invitation->setPlus($guestData['plus']);
				$invitation->setVip((isset($guestData['vip']) && $guestData['vip']==='vip') ? 1:0);
				$this->getInvitationRepository($invitation);
			}
		}
		return $this->redirectToRoute('event_manager');
	}

//************************************************************
	/**
	 * @return \AppBundle\Entity\User
	 */
	private function getLoggedUser() {
		return $this->get('security.token_storage')->getToken()->getUser();
	}

	/**
	 * @return \AppBundle\Repository\EventRepository
	 */
	private function getEventRepository(Event $event = NULL) {
		$em = $this->getDoctrine()->getManager();
		if(isset($event)){
			$event->setModified(new \DateTime);
			$em->persist($event);
			$em->flush();
			return TRUE;
		} else {
			return $em->getRepository('AppBundle:Event');
		}
	}

	/**
	 * @return \AppBundle\Repository\InvitationRepository
	 */
	private function getInvitationRepository(Invitation $invitation = NULL) {
		$em = $this->getDoctrine()->getManager();
		if(isset($invitation)){
			$invitation->setModified(new \DateTime());
			$em->persist($invitation);
			$em->flush();
			return TRUE;
		} else {
			return $em->getRepository('AppBundle:Invitation');
		}
	}

	/**
	 * @return \AppBundle\Repository\GuestGroupsRepository
	 */
	private function getGuestGroupRepository() {
		$em = $this->getDoctrine()->getManager();
		return $em->getRepository('AppBundle:GuestGroup');
	}
	/**
	 * @return \AppBundle\Repository\GuestRepository
	 */
	private function getGuestRepository() {
		$em = $this->getDoctrine()->getManager();
		return $em->getRepository('AppBundle:Guest');
	}
}
