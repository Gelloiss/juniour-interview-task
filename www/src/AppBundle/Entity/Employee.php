<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Employee")
 */
class Employee
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="string")
     */
    private $name;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Positions")
     * @ORM\JoinColumn(nullable=true, name="positionId", referencedColumnName="id")
     */
    private $position;
    /**
     * @ORM\Column(type="string")
     */
    private $photo;
    /**
     * @ORM\Column(type="float")
     */
    private $rate;
    /**
     * @ORM\Column(type="date")
     */
    private $firstDay;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Employee
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return Employee
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set photo.
     *
     * @param string $photo
     *
     * @return Employee
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo.
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set rate.
     *
     * @param float $rate
     *
     * @return Employee
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate.
     *
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set firstDay.
     *
     * @param \DateTime $firstDay
     *
     * @return Employee
     */
    public function setFirstDay(\DateTime $firstDay)
    {
        $this->firstDay = $firstDay;

        return $this;
    }

    /**
     * Get firstDay.
     *
     * @return \DateTime
     */
    public function getFirstDay()
    {
        return $this->firstDay;
    }
}
