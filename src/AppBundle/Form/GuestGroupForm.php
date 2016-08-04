<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Description of EventCreate
 *
 * @author Ľuboš Babocký
 */
class GuestGroupForm extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
				->add('name', TextType::class, array('label'=>FALSE))
				->add('submit', SubmitType::class);
	}
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => 'AppBundle\Entity\GuestGroup'));
	}
}
