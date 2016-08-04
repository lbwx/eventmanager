<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Description of UserType
 *
 * @author Ľuboš Babocký
 */
class UserEditForm extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
				->add('email', EmailType::class)
				->add('username', TextType::class)
				->add('avatar', FileType::class, ['label'=>'Avatar', 'required'=>FALSE])
				->add('submit', SubmitType::class, ['label'=>'Save']);
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => 'AppBundle\Entity\User'));
	}
}
