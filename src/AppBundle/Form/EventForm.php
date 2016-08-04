<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Description of EventCreate
 *
 * @author Ľuboš Babocký
 */
class EventForm extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('name', TextType::class, array('label' => 'Manager.Event.Name'))
			->add('maxGuests', IntegerType::class, array('label' => 'Manager.Event.MaxGuests'))
			->add('start', DateTimeType::class, array('label' => 'Manager.Event.Start', 'widget'=>'single_text', 'format'=>'yyyy.MM.dd HH:mm'))
			->add('end', DateTimeType::class, array('label' => 'Manager.Event.End', 'widget'=>'single_text', 'format'=>'yyyy.MM.dd HH:mm'))
			->add('place', TextType::class, array('label' => 'Manager.Event.Place'))
			->add('submit', SubmitType::class);
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event){
			
		});
	}
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => 'AppBundle\Entity\Event'));
	}
}
