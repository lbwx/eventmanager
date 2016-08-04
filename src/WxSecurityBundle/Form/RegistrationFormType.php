<?php
namespace WxSecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use AppBundle\Form\CompanyForm;

/**
 * Description of RegistrationFormType
 *
 * @author Ľuboš Babocký
 * @copyright (c) 2016, Webaholix, s.r.o
 */
class RegistrationFormType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
				->add('email', EmailType::class)
				->add('username', TextType::class)
				->add('plainPassword', RepeatedType::class, [
					'type' => PasswordType::class,
					'first_options' => ['label' => 'Password'],
					'second_options' => ['label' => 'Repeat Password']])
				->add('company', CompanyForm::class, []);
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'csrf_token_id' => 'registration'
		]);
	}

	public function getBlockPrefix() {
		return 'wx_user_registration';
	}
}
