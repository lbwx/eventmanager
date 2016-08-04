<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\CompanyEdit;



class CompanyManagerController extends Controller {

	/**
	 * @Route("/company/", name="company_manager")
	 */
	public function indexAction(Request $request) {
		$this->getCompanyUsers();
		
		return $this->render(
				'Pages/CompanyManager/index.html.twig',
				[
					'company' => $this->getLoggedUser()->getCompany(),
					'users' => $this->getCompanyUsers()
				]
			);
    }

	/**
	 * @Route("/company/edit/", name="company_manager_edit")
	 */
	public function editAction(Request $request) {
		$company = $this->getLoggedUser()->getCompany();
		$form = $this->createForm(CompanyEdit::class, $company);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()) {
			$this->getDoctrine()->getManager()->persist($company);
			$this->getDoctrine()->getManager()->flush();
			$this->addFlash('notice', 'flash.company.updated');
			return $this->redirectToRoute('company_manager');
		}
		return $this->render(
				'Pages/CompanyManager/editCompany.html.twig',
				array('form' => $form->createView())
			);
		
	}

	/**
	 * 
	 * @return \AppBundle\Entity\User
	 */
	private function getLoggedUser() {
		return $this->get('security.token_storage')->getToken()->getUser();
	}

	private function getCompanyUsers() {
		$em = $this->getDoctrine()->getManager();
		$userRepository = $em->getRepository('AppBundle:User');
		return $userRepository->findByCompany($this->getLoggedUser()->getCompany());
	}
/////////////////////////////////////////////////////////////////////
	/**
	 * @return \AppBundle\Repository\CompanyRepository
	 */
	private function getCompanyRepository() {
		$em = $this->getDoctrine()->getManager();
		return $em->getRepository('AppBundle:Company');
	}
}
