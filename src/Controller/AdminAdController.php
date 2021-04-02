<?php

namespace App\Controller;

use App\Entity\Ad;
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

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page}", name="admin_ads_index")
     */
    public function index(AdRepository $repo,$page=1): Response
    {
        $limit = 5;
        $start = ($page-1) * $limit;
        
            $totalAnnonce = count($repo->findAll());

            $pages = ceil($totalAnnonce/$limit);

            $ads=$repo->findBy([],[],$limit,$start);

        return $this->render('admin/ad/index.html.twig', [
            "ads"=>$ads,
            'nbannonce'=>$pages,
            'page'=>$page,
        ]);
    }

    /**
     * @Route("admin/ads/{slug}/edit", name="admin_ads_edit")
     *
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

                    foreach ($ad->getImages() as  $image) 
                    {
                        
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
                    foreach ($tabid as $id) 
                    {
                        foreach ($ad->getImageUploads() as $image) 
                        {
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
               return $this->redirectToRoute('admin_ads_index'); 

            }

        return $this->render('admin/ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad'=>$ad
        ]);
    }

    /**
     * @Route("/admin/ads/show/{slug}", name="admin_ads_show")
     */
    public function show(Ad $ad): Response
    {
        return $this->render('admin/ad/show.html.twig', [
            'ad'=>$ad
        ]);
    }

    /**
     *@Route("/admin/{slug}/delete", name="admin_ads_delete")
    * @IsGranted("ROLE_ADMIN")
     */
    public function delete(EntityManagerInterface $manager,Ad $ad): Response
    {

    $manager->remove($ad);
    $manager->flush();

     $this->addFlash(
            'success',
            'L\'annonce  a été correctement supprimée'
            );

            return $this->redirectToRoute('admin_ads_index');

    }

}


