<?php
namespace AppBundle\EventListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
/**
 * Description of LocaleListener
 *
 * @author Ľuboš Babocký
 */
class LocaleListener implements EventSubscriberInterface {

	public function onKernelRequest(GetResponseEvent $event) {
	//	dump($event->getRequest()->getSession());die;
		$request = $event->getRequest();
		$session = $event->getRequest()->getSession();
		if($session->get('language')) {
			$request->setLocale($session->get('language'));
		}
	}
	public static function getSubscribedEvents() {
		return array(KernelEvents::REQUEST => array(array('onKernelRequest', 15)));
	}
}
