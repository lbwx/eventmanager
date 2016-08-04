<?php

namespace AppBundle\Repository;

/**
 * GuestGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GuestGroupRepository extends \Doctrine\ORM\EntityRepository {
	public function findActualGuestGroups($companyId) {
		$em = $this->getEntityManager();
		$query = $em
				->createQuery('SELECT g FROM AppBundle:GuestGroup g WHERE g.company = :companyId AND g.deleted = 0 AND g.hidden = 0 ORDER BY g.created')
				->setParameter('companyId', $companyId);
		return $query->getResult();
	}
}