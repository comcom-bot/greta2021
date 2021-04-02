<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUsersController extends AbstractController
{
    /**
     * @Route("/admin/users", name="admin_user_index")
     */
    public function index(UserRepository $user): Response
    {

        return $this->render('admin/user/index.html.twig', [
        	'users'=>$user->findAll(),
         
        ]);
    }

    /**
     * @Route("/admin/users/delete/{slug}", name="admin_user_delete")
     */
    public function delete(User $user,EntityManagerInterface $manager): Response
    {
    	$manager->remove($user);
    	$manager->flush();
    	$this->addFlash("success","L'utilisateur a été suprimé");
    	return $this->redirectToRoute("admin_user_index");
       
    }
}
