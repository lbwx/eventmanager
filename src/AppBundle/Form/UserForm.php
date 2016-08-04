<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;




use AppBundle\Form\CompanyForm;

/**
 * Description of UserType
 *
 * @author Ľuboš Babocký
 */
class UserForm extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
				->add('email', EmailType::class)
				->add('username', TextType::class)
				->add('plainPassword', RepeatedType::class, array(
					'type' => PasswordType::class,
					'options' => array('translation_domain' => 'FOSUserBundle'),
					'first_options' => array('label' => 'form.password'),
					'second_options' => array('label' => 'form.password_confirmation'),
				))
				->add('company', CompanyForm::class, array(
					
				));
		
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => 'AppBundle\Entity\User'));
	}
}
