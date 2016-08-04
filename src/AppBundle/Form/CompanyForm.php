<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Description of EventCreate
 *
 * @author Ľuboš Babocký
 */
class CompanyForm extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, array('label' => 'company.name'))
			->add('address', TextType::class, array('label' => 'company.address'))
			->add('zip', TextType::class, array('label' => 'company.zip'))
			->add('city', TextType::class, array('label' => 'company.city'))
			->add('country', TextType::class, array('label' => 'company.country'));
	}
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => 'AppBundle\Entity\Company'));
	}
}
