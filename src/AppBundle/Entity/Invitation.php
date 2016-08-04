<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Invitation
 *
 * @ORM\Table(name="invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvitationRepository")
 */
class Invitation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Event", inversedBy="invitations")
	 */
	private $event;

	/**
	 * @ORM\ManyToOne(targetEntity="Guest", inversedBy="invitations")
	 */
	private $guest;

	/**
	 * @var int
	 * 
	 * @ORM\Column(name="plus", type="smallint", options={"default":"0"})
	 */
	private $plus;

	/**
	 * @var boolean
	 * 
	 * @ORM\Column(name="vip", type="boolean", options={"default":"0"})
	 */
	private $vip;

	/**
	 * @var boolean
	 * 
	 * @ORM\Column(name="checkin", type="boolean", options={"default":"0"})
	 */
	private $checkin;

	/**
	 * @var \DateTime
	 * 
	 * @ORM\Column(name="created", type="datetime")
	 */
	private $created;

	/**
	 * @var \DateTime
	 * 
	 * @ORM\Column(name="modified", type="datetime")
	 */
	private $modified;

	/**
	 * @var bool
	 * 
	 * @ORM\Column(name="hidden", type="boolean", options={"default": "0"})
	 */
	private $hidden;

	/**
	 * @var bool
	 * 
	 * @ORM\Column(name="deleted",type="boolean", options={"default": "0"})
	 */
	private $deleted;

	public function __construct() {
		
		
		$this->checkin = FALSE;
		$this->created = new \DateTime();
		$this->modified = new \DateTime();
		$this->hidden = FALSE;
		$this->deleted = FALSE;
			
	}

	/**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set plus
     *
     * @param integer $plus
     *
     * @return Invitation
     */
    public function setPlus($plus)
    {
        $this->plus = $plus;

        return $this;
    }

    /**
     * Get plus
     *
     * @return integer
     */
    public function getPlus()
    {
        return $this->plus;
    }

    /**
     * Set vip
     *
     * @param boolean $vip
     *
     * @return Invitation
     */
    public function setVip($vip)
    {
        $this->vip = $vip;

        return $this;
    }

    /**
     * Get vip
     *
     * @return boolean
     */
    public function getVip()
    {
        return $this->vip;
    }

    /**
     * Set checkin
     *
     * @param boolean $checkin
     *
     * @return Invitation
     */
    public function setCheckin($checkin)
    {
        $this->checkin = $checkin;

        return $this;
    }

    /**
     * Get checkin
     *
     * @return boolean
     */
    public function getCheckin()
    {
        return $this->checkin;
    }

    /**
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Invitation
     */
    public function setEvent(\AppBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set guest
     *
     * @param \AppBundle\Entity\Guest $guest
     *
     * @return Invitation
     */
    public function setGuest(\AppBundle\Entity\Guest $guest = null)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * Get guest
     *
     * @return \AppBundle\Entity\Guest
     */
    public function getGuest()
    {
        return $this->guest;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Invitation
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return Invitation
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return Invitation
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return Invitation
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
