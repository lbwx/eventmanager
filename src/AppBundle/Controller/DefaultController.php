<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller {
	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request) {
		return $this->render('Templates/Default/index.html.twig', ['base_dir' => realpath($this->getParameter('kernel.root_dir').'/..')]);
	}

	/**
	 * @Route("/lang/{language}", name="change_language", defaults={"language" = "en"})
	 */
	public function changeLanguageAction(Request $request, $language) {
		$session = $request->getSession();
		$session->set('language', $language);
		return $this->redirect($request->headers->get('referer'));
	}
}
