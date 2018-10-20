<?php

namespace AppBundle\Entity;

class SkipAdd
{
    protected $employeeId;
    protected $date;

    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }
}