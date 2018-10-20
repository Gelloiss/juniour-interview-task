<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile as UploadedFile;

class EmployeeEdit
{
    protected $employeeId;
    protected $name;
    protected $position;
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Загрузите изображение")
     * @Assert\File(mimeTypes={ 
     *      "image/png",
     *      "image/jpeg",
     *      "image/jpg",
     *      "image/gif", 
     *  })
     */
    protected $photo;
    protected $rate;
    protected $firstDay;

    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function setEdmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition(Positions $position){
        $this->position = $position;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto(\Symfony\Component\HttpFoundation\File\UploadedFile $photo)
    {
        $this->photo = $photo;

        return $this;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    public function getFirstDay()
    {
        return $this->firstDay;
    }

    public function setFirstDay($firstDay)
    {
        $this->firstDay = $firstDay;
    }
}