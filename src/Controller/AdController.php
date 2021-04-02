<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Entity\ImageUpload;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Services\ImagesUploadService;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo): Response
    {

    	//$repo=$this->getDoctrine()->getRepository(Ad::class);

    	//$ad=$repo->findOneById(2);
    	//$ad=$repo->findOneBySlug("titre-de-l-annonce-n0.4_4");
    	//$ad=$repo->findByRooms(2);
    	$ads=$repo->findAll();

    	//dump($ads);

        return $this->render('ad/index.html.twig', [
            'ads'=> $ads
        ]);
    }


	/**
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     */
    public function create(EntityManagerInterface $manager, Request $request,ImagesUploadService $upload): Response
    {


    	$ad = new Ad();



    	$form = $this -> createForm(AnnonceType::class,$ad);

    	$form -> handleRequest($request);
 

		

    		if ($form->isSubmitted() && $form->isValid())
    	
    		{
                // enregistrement de l'auteur de l'annonce
               $ad->setAuthor($this->getUser());

    			$slugify = new Slugify();
    			$slug = $slugify->slugify($ad->getTitle());
    			$ad->setSlug($slug);


		    		foreach ($ad->getImages() as  $image) {
		    		
		    		$image->setAd($ad);
		    		$manager->persist($image);
		    		//dump($image);

		    		}


             // upload de fichiers déporté dans Services        
            $upload->upload($ad,$manager);




    		$manager->persist($ad);
    		$manager->flush();

    		$ad->setSlug($ad->getSlug().'_'.$ad->getId());
    		$manager->persist($ad);
    		$manager->flush();


    		 $this->addFlash(
            'success',
            'L\'annonce '.$ad->getTitle().' a été correctement enregistrée'
        	);


    		return $this->redirectToRoute('ads_show',['slug'=>$ad->getSlug()]);

    		}


    	
        return $this->render('ad/new.html.twig', [
          	'form' => $form->createView()
        ]);
    }


/**
     * @Route("/ads/{slug}/edit", name="ads_edit")
     *@Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="cette annonce ne vous appartient pas")
     */
    public function edit(EntityManagerInterface $manager, Request $request,Ad $ad,ImagesUploadService $upload): Response
    {
   

    	$form = $this -> createForm(AnnonceType::class,$ad);

    	$form -> handleRequest($request);
 

    		if ($form->isSubmitted() && $form->isValid())
    	
    		{


    			$slugify = new Slugify();
    			$slug = $slugify->slugify($ad->getTitle());
    			$ad->setSlug($slug);


		    		foreach ($ad->getImages() as  $image) {
		    		
		    		$image->setAd($ad);
		    		$manager->persist($image);
		    		//dump($image);

		    		}


                    // upload de fichiers déporté dans Services        
            $upload->upload($ad,$manager);

            
            // extraction des id des images en dures à supprimer
            $tabid=$ad->tableau_id;
            $tabid = preg_replace("#^,#",'',$tabid);
            $tabid=explode(",",$tabid);

            
    
          //dump($_SERVER);
//exit;
           foreach ($tabid as $id) {
              

                    foreach ($ad->getImageUploads() as $image) {
                                //dump($image->getId());

                                if ($id==$image->getId())

                                {

                                    $manager->remove($image);
                                    $manager->flush();

                                    unlink($_SERVER['DOCUMENT_ROOT'].$image->getUrl());


                                }



                            }


            }

    		$manager->persist($ad);
    		$manager->flush();

    		$ad->setSlug($ad->getSlug().'_'.$ad->getId());
    		$manager->persist($ad);
    		$manager->flush();


    		 $this->addFlash(
            'success',
            'L\'annonce '.$ad->getTitle().' a été correctement modifié'
        	);


    		return $this->redirectToRoute('ads_show',['slug'=>$ad->getSlug()]);

    		}


    	
        return $this->render('ad/edit.html.twig', [
          	'form' => $form->createView(),
          	'ad'=>$ad
        ]);
    }


	/**
     * @Route("/ads/{slug}", name="ads_show")
     */
    public function show(Ad $ad): Response
    {

    	//$repo=$this->getDoctrine()->getRepository(Ad::class);

    	//$ad=$repo->findOneById(2);
    	//$ad=$repo->findOneBySlug("titre-de-l-annonce-n0.4_4");
    	//$ad=$repo->findByRooms(2);
    	//$ads=$repo->findAll();
		//$ad=$repo->findOneBySlug($slug);  // 1 seul critère
		//$ad=$repo->findOneBy(['slug'=>$slug]);  // eventuellement plusieurs critères

    	//dump($ad);



        return $this->render('ad/show.html.twig', [
            'ad'=>$ad
        ]);
    }
    /**
     *@Route("/ads/{slug}/delete", name="ads_delete")
     *@Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message="Vous ne pouvez pas supprimer cette annonce. Elle ne vous appartient pas")
     */
    public function delete(EntityManagerInterface $manager,Ad $ad): Response
    {

   	$manager->remove($ad);
   	$manager->flush();

	 $this->addFlash(
            'success',
            'L\'annonce  a été correctement supprimée'
        	);


    		return $this->redirectToRoute('ads_index');

    }

}
