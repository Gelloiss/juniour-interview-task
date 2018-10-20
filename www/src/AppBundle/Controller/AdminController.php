<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Positions;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\PositionsForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\EmployeeAdd;
use AppBundle\Entity\EmployeeEdit;
use AppBundle\Entity\Employee;
use AppBundle\Entity\Skip;
use AppBundle\Entity\SkipAdd;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Knp\Component\Pager\PaginatorInterface;
use AppBundle\Entity\Salary;

class AdminController extends Controller
{
	/**
     *
     * @Route("/admin/positions", name="adminPositions")
     */
	public function positionsAdd(Request $request)
	{
		$positionsForm = new PositionsForm();
		$positionsForm->setName('');

		$form = $this->createFormBuilder($positionsForm)
			->add('name', TextType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Название должности', 
                    'class' => 'form-control',
                ))
			)
			->add('save', SubmitType::class, array(
				'label' => 'Добавить должность',
				'attr' =>  array(
					'class' => 'btn btn-default w3ls-button',
				)
			))
		    ->getForm();

		$form->handleRequest($request);

		//Input db
	    if ($form->isSubmitted() && $form->isValid()) {
	        $task = $form->getData();
	        $em = $this->getDoctrine()->getManager();
	        $positions = new Positions();
	        $positions->setName($form['name']->getData());
	        $em->persist($positions);
	        $em->flush();
	    }

	    //Output db
	    $repository = $this->getDoctrine()->getRepository(Positions::class);
	    $positions = $repository->findAll();

		return $this->render('admin/sectionPositions.html.twig', array(
		    'form' => $form->createView(),
		    'positions' => $positions,
		));
	}

	/**
	 * @Route("/admin/position/del/{objid}", name="adminPositionsDel")
	 */
	public function deletePosition($objid)
	{
	   	$em = $this->getDoctrine()->getManager();
	  	//find
        $repository = $this->getDoctrine()->getRepository(Positions::class);
        $position = $repository->find($objid);

        //del
        $em->remove($position);
		$em->flush();

		return $this->redirect($this->generateUrl('adminPositions'));
    }

	/**
     *
     * @Route("/admin/employee/add", name="adminEmployeeAdd")
     */
	public function employeeAdd(Request $request)
	{
		$adding = false;
		$errorRate = false;
		$employeeAdd = new employeeAdd();
		$form = $this->createFormBuilder($employeeAdd)
            ->add('name', TextType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Имя сотрудника', 
                    'class' => 'form-control',
                ))
        	)
            ->add('rate', TextType::class, array (
            	'label' => false,
			    'attr' => array(
			       'placeholder' => 'Ставка в час',
			       'class' => 'form-control',
			    ))
			)
            ->add('firstDay', TextType::class, array(
            	 'label' => false,
                 'attr' => array(
                 	'placeholder' => 'Дата первого рабочего дня',
                 	'readonly' => 'readonly',
                 	'class' => 'form-control calendar',
                 ))
            )
            ->add('position', EntityType::class, array(
            	'label' => false,
            	'class' => Positions::class,
            	'choice_label' => 'name',
            	'attr' => array(
            		'class' => 'form-control',
            	))
			)
			->add('photo', FileType::class, array('label' => 'Добавить фотографию'))
			->add('save', SubmitType::class, array(
				'label' => 'Добавить сотрудника',
				'attr' =>  array(
					'class' => 'btn btn-default w3ls-button',
				)
			))
			->getForm();

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if (is_numeric(floatval($form['rate']->getData()))) {
				$em = $this->getDoctrine()->getManager();
		        $employee = new Employee();
		        $employee->setName($form['name']->getData());
		        $employee->setPosition($form['position']->getData());
		        $employee->setRate($form['rate']->getData());
		        $firstDay = \DateTime::createFromFormat('d/m/Y', $form['firstDay']->getData());
		        $employee->setFirstDay($firstDay);
		        $file = $employeeAdd->getPhoto();
		        $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
		        $file->move(
	                $this->getParameter('files_directory'),
	                $fileName
	            );
	            $employee->setPhoto($fileName);
	            $em->persist($employee);
	            $em->flush();
	            $adding = true;
			}
			else {
				$errorRate = true;
			}
	    }

		return $this->render('admin/employeeAdd.html.twig', array(
          	'form' => $form->createView(),
           	'adding' => $adding,
           	'errorRate' => $errorRate,
        ));
	}

	/**
	 * @Route("/admin/skip/{idEmployee}", name="adminSkip")
	 */
	public function skipPage($idEmployee, Request $request)
	{
		$adding = false;
		$em = $this->getDoctrine()->getManager();
		$employee = $this->getDoctrine()->getRepository(employee::class);
	    $employee = $employee->find($idEmployee);
	    $employeeId = $employee->getId();

	    $skipAdd = new SkipAdd();
	    $skipAdd->setEmployeeId('');
        $skipAdd->setDate('');

        $form = $this->createFormBuilder($skipAdd)
            ->add('employeeId', HiddenType::class, array (
            	'label' => false,
			    'attr' => array(
			       'value' => $employeeId,
			       'class' => 'form-control', 
			    ))
        	)
            ->add('date', TextType::class, array(
            	 'label' => false,
                 'attr' => array(
                 	'placeholder' => 'День отсутствия',
                 	'readonly' => 'readonly',
                 	'class' => 'form-control calendar',
                 ))
        	)
            ->add('save', SubmitType::class, array(
				'label' => 'Добавить пропуск',
				'attr' =>  array(
					'class' => 'btn btn-default w3ls-button',
				))
        	)
            ->getForm();

            $form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$em = $this->getDoctrine()->getManager();
		        $skip = new Skip();
		        $skip->setEmployeeId($form['employeeId']->getData());
		        $date = \DateTime::createFromFormat('d/m/Y', $form['date']->getData());
		        $skip->setDate($date);
	            $em->persist($skip);
	            $em->flush();
	            $adding = true;
		    }

	    return $this->render('admin/adminSkip.html.twig', array(	        
	        	'employee' => $employee,
	        	'form' => $form->createView(),
	        	'adding' => $adding,
	    	));
	}

	/**
	 * @Route("/admin/editEmployee/{idEmployee}", name="editEmployee")
	 */
	public function editEmployee($idEmployee, Request $request)
	{
		$edditing = false;
		$em = $this->getDoctrine()->getManager();
		$employee = $this->getDoctrine()->getRepository(Employee::class);
	    $employee = $employee->find($idEmployee);
	    $employeeId = $employee->getId();

	    $employeeEdit = new EmployeeEdit();
	    $employeeName = $employee->getName();
	    $employeePosition = $employee->getPosition();
	    $employeePhoto = $employee->getPhoto();
	    $employeeRate = $employee->getRate();
	    $employeeFirstDay = $employee->getFirstDay();
	    $employeeFirstDay = $employeeFirstDay->format('d/m/Y');

		$form = $this->createFormBuilder($employeeEdit)
			->add('employeeId', HiddenType::class, array (
            	'label' => false,
            	'mapped' => false,
			    'attr' => array(
			       'value' => $employeeId,
			       'class' => 'form-control', 
			    ))
        	)
            ->add('name', TextType::class, array(
                'label' => false,
                'attr' => array(
                	'value' => $employeeName,
                    'placeholder' => 'Имя сотрудника', 
                    'class' => 'form-control',
                ))
        	)
            ->add('rate', TextType::class, array (
            	'label' => false,
			    'attr' => array(
			    	'value' => $employeeRate,
			       'placeholder' => 'Ставка в час',
			       'class' => 'form-control', 
			       'min' => 1,
			       'step' => 0.01,
			    ))
			)
            ->add('firstDay', TextType::class, array(
            	 'label' => false,
                 'attr' => array(
                 	'value' => $employeeFirstDay,
                 	'placeholder' => 'Дата первого рабочего дня',
                 	'readonly' => 'readonly',
                 	'class' => 'form-control calendar',
                 ))
            )
            ->add('position', EntityType::class, array(
            	'label' => false,
            	'class' => Positions::class,
            	'choice_label' => 'name',
            	'attr' => array(
            		'class' => 'form-control',
            	))
			)
			->add('photo', FileType::class, array('label' => 'Добавить фотографию'))
			->add('save', SubmitType::class, array(
				'label' => 'Обновить информацию',
				'attr' =>  array(
					'class' => 'btn btn-default w3ls-button',
				)
			))
			->getForm();

			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$employeeEdit = new Employee();
				$em = $this->getDoctrine()->getManager();
		        $repository = $this->getDoctrine()->getRepository(Employee::class);
		        $employeeEdit = $repository->find($form['employeeId']->getData());
		        $date = \DateTime::createFromFormat('d/m/Y', $form['firstDay']->getData());
		        $file = $form->get('photo')->getData();
		        $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
		        $file->move(
	                $this->getParameter('files_directory'),
	                $fileName
	            );

		        $employeeEdit->setName($form['name']->getData());
		        $employeeEdit->setPosition($form['position']->getData());
		        $employeeEdit->setPhoto($fileName);
		        $employeeEdit->setRate($form['rate']->getData());
		        $employeeEdit->setFirstDay($date);
	            $em->flush();
	            $edditing = true;
		    }

	    return $this->render('admin/editEmployee.html.twig', array(	        
	        	'employee' => $employee,
	        	'form' => $form->createView(),
	        	'edditing' => $edditing,
	    	));
	}

	/**
	 * @Route("/admin/getSalary/{idEmployee}", name="getSalary")
	 */
	public function getSalary($idEmployee, Request $request)
	{
		$totalRate = 0;
		$errorMonth = false;
		$errorYear = false;
		$em = $this->getDoctrine()->getManager();
		$employee = $this->getDoctrine()->getRepository(employee::class);
	    $employee = $employee->find($idEmployee);
	    $employeeId = $employee->getId();
	    $salary = new Salary();

	    $months = array_combine($r = range(1, 12), $r);
	    $years = array_combine($r = range(date('Y'), date('Y') - 10), $r);

        $form = $this->createFormBuilder($salary)
            ->add('salaryMonth', ChoiceType::class, array('label' => false, 'choices' => $months))
            ->add('salaryYear', ChoiceType::class, array('label' => false, 'choices' => $years))
            ->add('save', SubmitType::class, array('label' => 'Загрузить'))
            ->getForm();

        $form->handleRequest($request);
	    if ($form->isSubmitted() && $form->isValid()) {
	    	$month = $form['salaryMonth']->getData();
	    	$year = $form['salaryYear']->getData();
			$em = $this->getDoctrine()->getManager();
			$employee = $this->getDoctrine()->getRepository(employee::class);
		    $employee = $employee->find($idEmployee);
		    $employeeFirstDay = $employee->getFirstDay();
		    $employeeId = $employee->getId();
		    $employeeRate = $employee->getRate();
		    $repository = $this->getDoctrine()->getRepository(skip::class);
		    $skip = $repository->findByEmployeeId($employeeId);
		    $countSkip = count($skip);
		    $firstMonth = $employeeFirstDay->format('n');
		    $firstDay = $employeeFirstDay->format('j');
		    $nowDate = new \DateTime('');
		    $nowMonth = $nowDate->format('n');
		    $nowDay = $nowDate->format('j');
		    $firstYear = $employeeFirstDay->format('Y');
		    $nowYear = $nowDate->format('Y');

		    if ($year < $firstYear) {
		    	$errorYear = true;
		    }
		    else {
			    if ($year == $nowYear) {
			    	if (($firstMonth < $month) && ($nowMonth >= $month)) {
			    		$countSkipDay = 0;
			    		for ($i=0; $i < $countSkip; $i++) { 
			    			$buf = $skip[$i]->getDate();
			    			$bufMonth = $buf->format('n');
			    			$bufYear = $buf->format('Y');
			    			if (($month == $bufMonth) && ($year == $bufYear)) {
			    				$countSkipDay++;
			    			}
			    		}
			    		$countWorkingDay = 20 - $countSkipDay;
			    		$countDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			    		for ($i=0; $i < $countDay-28; $i++) { 
			    			$bufDate = \DateTime::createFromFormat('Y-n-j', $year.'-'.$month.'-'.$i);
			    			$bufDayName = $bufDate->format('D');
			    			if (($bufDayName != 'Sun') || ($bufDayName != 'Sat')) {
			    				$countWorkingDay++;
			    			}
			    		}
			    		$totalRate = $countWorkingDay*8*$employeeRate;
			    	}
			    	elseif (($firstMonth == $month) && ($nowMonth >= $month)) {
			    		$countSkipDay = 0;
		    			for ($i=0; $i < $countSkip; $i++) { 
			    			$buf = $skip[$i]->getDate();
			    			$bufMonth = $buf->format('n');
			    			$bufYear = $buf->format('Y');
			    			if (($month == $bufMonth) && ($year == $bufYear)) {
			    				$countSkipDay++;
			    			}
		    			}
		    			$countWorkingDay = 20 - $countSkipDay - $firstDay;
		    			$countDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		    			for ($i=$firstDay; $i < $countDay-28; $i++) { 
			    			$bufDate = \DateTime::createFromFormat('Y-m-j', $year.'-'.$month.'-'.$i);
			    			$bufDayName = $bufDate->format('D');
			    			if (($bufDayName != 'Sun') || ($bufDayName != 'Sat')) {
			    				$countWorkingDay++;
			    			}
			    		}
			    		$totalRate = $countWorkingDay*8*$employeeRate;
			    	}

			    	elseif ($nowMonth <= $month) {
			    		$errorMonth = true;
			    		$totalRate = 0;
			    	}
			    }

			    else {
			    	$countSkipDay = 0;
		    		for ($i=0; $i < $countSkip; $i++) { 
		    			$buf = $skip[$i]->getDate();
		    			$bufMonth = $buf->format('n');
		    			$bufYear = $buf->format('Y');
		    			if (($month == $bufMonth) && ($year == $bufYear)) {
		    				$countSkipDay++;
		    			}
		    		}
		    		$countWorkingDay = 20 - $countSkipDay;
		    		$countDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		    		for ($i=0; $i < $countDay-28; $i++) { 
		    			$bufDate = \DateTime::createFromFormat('Y-n-j', $year.'-'.$month.'-'.$i);
		    			$bufDayName = $bufDate->format('D');
		    			if (($bufDayName != 'Sun') || ($bufDayName != 'Sat')) {
		    				$countWorkingDay++;
		    			}
		    		}
		    		$totalRate = $countWorkingDay*8*$employeeRate;
			    }
			}
		}


	    return $this->render('admin/getSalary.html.twig', array(	
	    		'form' => $form->createView(),
	        	'employee' => $employee,
	        	'employeeId' => $employeeId,
	        	'totalRate' => $totalRate,
	        	'errorMonth' => $errorMonth,
	        	'errorYear' => $errorYear,
	    	));
	}

	/**
     * @Route("/admin/{page}", name="adminEmployeeAll")
     */
	public function adminEmployeeAll($page, Request $request)
	{
	    $em = $this->get('doctrine.orm.entity_manager');

	    $count = $em
    		->createQuery('SELECT COUNT(c) FROM AppBundle:Employee c')
    		->getSingleScalarResult();

	    $dql = "SELECT e, p FROM AppBundle:Employee e JOIN e.position p";
	    $query = $em
	    	->createQuery($dql)
	    	->setHint(
		        'knp_paginator.count', 
		        $count
    		);


	    $paginator  = $this->get('knp_paginator');
	    $pagination = $paginator->paginate(
	        $query, /* query NOT result */
	        $request->query->getInt('page', $page) /*page number*/,
	        5 /*limit per page*/,
	        array(
        		'distinct' => false,
    		)
	    );

	    return $this->render('admin/adminEmployeeAll.html.twig', 
	        [
	        	'pagination' => $pagination,
	    	]);
	}

	/**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Employee::class,
        ));
    }
}