<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminBookingController extends AbstractController
{
    /**
     * @Route("/admin/booking/{page}", name="admin_booking_index")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(BookingRepository $repo,$page=1): Response
    {
     	$limit = 10;
     	$start = ($page -1 )*$limit;
     	$nombrereservatiion = count($repo->findAll());
     	$nombrePage = ceil($nombrereservatiion/$limit);

        return $this->render('admin/booking/index.html.twig', [
        	'bookings'=>$repo->findBy([],[],$limit,$start),
        	'page'=>$page, 
        	'pages'=>$nombrePage,
           
        ]);
    }


    /**
     * @Route("/admin/booking/show/{id}", name="admin_booking_show")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Booking $booking,EntityManagerInterface $manager): Response
    {
            
 			return $this->render('admin/booking/show.html.twig', [
       		'booking'=>$booking, 
           
        ]);
    }



    /**
     * @Route("/admin/booking/delete/{id}", name="admin_booking_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Booking $booking,EntityManagerInterface $manager): Response
    {
           
 		$manager->remove($booking);
 		$manager->flush();
		$this->addFlash("success", "La reservation  a étée supprimée."); 
		return $this->redirectToRoute('admin_booking_index');
    }
}
