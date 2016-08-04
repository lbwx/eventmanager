<?php
namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

use AppBundle\Repository\GuestGroupRepository;

/**
 * Description of EventCreate
 *
 * @author Ľuboš Babocký
 */
class GuestForm extends AbstractType {
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
				->add('firstName', TextType::class, array('label' => 'Manager.Guest.FirstName'))
				->add('lastName', TextType::class, array('label'=>'Manager.Guest.LastName'))
				->add('title', TextType::class, array('label'=>'Manager.Guest.Title', 'required'=>FALSE))
				->add('employer', TextType::class, array('label'=>'Manager.Guest.Employer', 'required'=>FALSE))
				->add('plus', IntegerType::class, array('label'=>'Manager.Guest.Plus', 'required'=>FALSE))
				->add('vip', CheckboxType::class, array('label'=>'Manager.Guest.Vip', 'required'=>FALSE))
				->add('info', TextareaType::class, array('label'=>'Manager.Guest.Info', 'required'=>FALSE))
				->add('address', TextType::class, array('label'=>'Manager.Guest.Address', 'required'=>FALSE))
				->add('city', TextType::class, array('label'=>'Manager.Guest.City', 'required'=>FALSE))
				->add('zip', TextType::class, array('label'=>'Manager.Guest.Zip', 'required'=>FALSE))
				->add('country', TextType::class, array('label'=>'Manager.Guest.Country', 'required'=>FALSE))
				->add('phone', TextType::class, array('label'=>'Manager.Guest.Phone', 'required'=>FALSE))
				->add('mobilePhone', TextType::class, array('label'=>'Manager.Guest.MobilePhone', 'required'=>FALSE))
				->add('email', EmailType::class, array('label'=>'Manager.Guest.Email', 'required'=>FALSE))
				->add('guestgroup', EntityType::class, [
					'label' => 'Manager.Guest.GuestGroup',
					'class' => 'AppBundle:GuestGroup',
					'query_builder' => function(EntityRepository $er) {
						return $er->createQueryBuilder('gg')
								->where('gg.deleted=0')
								->andWhere('gg.hidden=0')
								->orderBy('gg.created', 'ASC');
					},
					'choice_label' => 'name'
				])
				->add('submit', SubmitType::class);
	}
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array('data_class' => 'AppBundle\Entity\Guest'));
	}
}
