<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{

 /**
     * @Route("/register", name="account_register")
     */
    public function register(EntityManagerInterface $manager, Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {

		$user = new User();


    	$form = $this -> createForm(RegistrationType::class,$user);

    	$form -> handleRequest($request);
 
		if ($form->isSubmitted() && $form->isValid())
    	
    		{

    	

    			$slugify = new Slugify();
    			$slug = $slugify->slugify($user->getFirstName().'-'.$user->getLastName());
    			$user->setSlug($slug);

    			$password = $user->getHash();
    			$user->setHash($passwordEncoder->encodePassword(
           					 $user,
           					$password
            				));

    			$manager->persist($user);
				$manager->flush();

				 $this->addFlash(
            'success',
            'L\'utilisateur '.$user->getFirstName().' '.$user->getLastName().' a été correctement enregistré'
        	);


				 return $this->redirectToRoute('account_login');


    		}


    		return $this->render('account/registration.html.twig', [
          	'form' => $form->createView()
        ]);

    }



    /**
     * @Route("/login", name="account_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

    	$error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('account/login.html.twig', [
        	'hasError'=>$error
            
        ]);
    }




  /**
     * @Route("/account/profile", name="account_profile")
     */
    public function profile(EntityManagerInterface $manager, Request $request): Response
    {
        // recupération de l'utilisateur connecté
        $user = $this->getUser();

        $form = $this -> createForm(AccountType::class,$user);

        $form -> handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid())
        
            {




                $slugify = new Slugify();
                $slug = $slugify->slugify($user->getFirstName().'-'.$user->getLastName());
                $user->setSlug($slug);

                $manager->persist($user);
                $manager->flush();


                 $this->addFlash(
            'success',
            'Le profil de  '.$user->getFirstName().' '.$user->getLastName().' a été modifié'
            );


                 // redirection vers l'affichage de profil

            }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);

    }  



  /**
     * @Route("/account/", name="account_index")
     */
    public function myAccount(): Response
    {


 return $this->render('user/index.html.twig', [
            'user'=> $this->getUser()
        ]);

    }



      /**
     * @Route("/account/password-update", name="account_password")
     */
    public function updatePassword(EntityManagerInterface $manager, Request $request,UserPasswordEncoderInterface $passwordEncoder): Response
    {

        // recupération de l'utilisateur connecté
        $user = $this->getUser();

        $passwordUpdate = new PasswordUpdate();

        $form = $this -> createForm(PasswordUpdateType::class,$passwordUpdate);

        $form -> handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid())
        
            {

                // verification que oldpassword est le même que celui de user
                if (password_verify($passwordUpdate->getOldPassword(), $user->getHash()))
                {

                  $newPassword = $passwordUpdate->getNewPassword();
                $user->setHash($passwordEncoder->encodePassword(
                             $user,
                            $newPassword
                            ));

                    $manager->persist($user);
                    $manager->flush();

                     $this->addFlash(
            'success',
            'Le mot de passe a bien été modifié'
            );

                     return $this->redirectToRoute('homepage'); // a modifié

                }
                else{


                     $this->addFlash(
            'danger',
            'L\'ancien mot de passe est incorrect '
            );



                }



            }

     return $this->render('account/password.html.twig', [
            'form' => $form->createView()
        ]);


    } 



     /**
     * @Route("/logout", name="account_logout")
     */
    public function logout():response
    {
      
    }





}
