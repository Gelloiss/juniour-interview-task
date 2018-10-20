<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Positions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityManagerInterface;

class AdminPositionDelController extends Controller
{
	/**
     * @Route("/admin/position/del0/{objid}", name="adminPositionsDel0")
     */

	public function del($objid)
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
}