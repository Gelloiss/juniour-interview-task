<?php
namespace AppBundle\Entity;

class Salary
{
    protected $salaryMonth;
    protected $salaryYear;

    public function getSalaryMonth()
    {
        return $this->salaryMonth;
    }

    public function setSalaryMonth($salaryMonth)
    {
        $this->salaryMonth = $salaryMonth;
    }

    public function getSalaryYear()
    {
        return $this->salaryYear;
    }

    public function setSalaryYear($salaryYear)
    {
        $this->salaryYear = $salaryYear;
    }
}