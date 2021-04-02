<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\BookingType;
use App\Form\CommentType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     */
    public function book(EntityManagerInterface $manager, Request $request,Ad $ad,BookingRepository $repo): Response
    {
    	//******************  début liste des dates non disponibles pour la réservation *************

    	// initialisation du tableau contenant les dates non disponibles
    	$notAvailableDays=[];

    	// récupération de toutes les réservations déjà existantes
    	// on utilise findBy qui permet de trouver plusieurs enregistrement avec 4 paramètres.
    	//   - Critères de sélection
    	//   - Ordres  (tri)
    	//  - Limit ( nombre d'enregistrements)
    	// -  offset ( à partir de où on part)
    	//
    	// exemple:  findBy(['author'=>"44"],["rooms"=>desc],2,0 )

    	$repo = $repo->findBy(['ad'=>$ad->getId()]);
    	foreach ($repo as $book) {
    		
    		$tous_les_timestamp = range($book->getStartDate()->getTimestamp(),$book->getEndDate()->getTimestamp(),24*3600);

    		// on enlève la dernière date de chaque réservation car elle est réservable
    		 array_pop($tous_les_timestamp);

    		$notAvailableDays = array_merge($notAvailableDays,$tous_les_timestamp);
        }

        //dd($tous_les_timestamp);
    	//******************  fin liste des dates non disponibles pour la réservation *************
    	


    	$booking = new Booking();

		$form = $this -> createForm(BookingType::class,$booking);

    	$form -> handleRequest($request);


    	if ($form->isSubmitted() && $form->isValid())
    	{
            // dd($booking);
    			$user = $this->getUser();
    			$booking->setBooker($user)
    					->setCreatedAt(new \DateTime())
    					->setAd($ad);

    			$interval = date_diff($booking->getStartDate(), $booking->getEndDate());	
    			
    			$booking->setAmount($ad->getPrice()*($interval->days ));

    			// récupération des timestamp des dates du formulaire listé jour par jour 24*3600 = un jour
    			$tous_les_timestamp_choisis = range($booking->getStartDate()->getTimestamp(),$booking->getEndDate()->getTimestamp(),24*3600);

    			// on supprime la derniere date car on ne reste pas la nuit
    			array_pop($tous_les_timestamp_choisis);

    			$datesOK=true;

    			dump($notAvailableDays);
    			dump($tous_les_timestamp_choisis);

    			foreach ($tous_les_timestamp_choisis as $value) {
    		
    				// si différent de false alors au moins une date est trouvée
    				if (array_search($value,$notAvailableDays)!==false) {$datesOK=false;break;}


    			}


    			if (!$datesOK){

		    				$this->addFlash(
		            'warning',
		            'Les dates choisies ne sont pas disponibles'
		        	);

    			} else 
    		

    				{
		    			//dump($booking);
		    			$manager->persist($booking);
		    			$manager->flush();
$this->addFlash('success','La réservation  a bien été effectuée');

		    			return $this->redirectToRoute('booking_show',['id'=>$booking->getId()]);

    				}

    		}

        return $this->render('booking/book.html.twig', [
           'form' => $form->createView(),
           
           'ad'=>$ad,
           'notAvailableDays'=>$notAvailableDays,
        ]);
    }

// affichage de toutes les réservations
/**
     * @Route("/bookings/{id}", name="booking_index")
     * @IsGranted("ROLE_USER")
     */
    public function bookings(User $booker): Response
    {




		return $this->render('booking/index.html.twig', [
         'booker'=>$booker
        ]);
    }



    /**
     * @Route("/booking/{id}", name="booking_show")
     * @IsGranted("ROLE_USER")
     */
    public function show(Booking $booking,EntityManagerInterface $manager,Request $request): Response
    {
            $comment = new Comment();
            $form = $this->createForm(CommentType::class,$comment);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid())
            {
                $comment->setAd($booking->getAd())
                        ->setAuteur($this->getUser())
                        ->setCreateadAt(new DateTime())
                        ;
                $manager->persist($comment);
                $manager->flush();
                $this->addFlash("success","Votre commentaire est publié");


            }
 			return $this->render('booking/show.html.twig', [
       		'booking'=>$booking, 
            'form'=>$form->createView(),
        ]);
    }


}
