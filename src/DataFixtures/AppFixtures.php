<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Role;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

private $passwordEncoder;

            public function __construct(UserPasswordEncoderInterface $passwordEncoder)
            {
                 $this->passwordEncoder = $passwordEncoder;
           }

    public function load(ObjectManager $manager)
    {


// creation d'un admin
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
         $adminUser->setFirstName("Jeannot")
            ->setLastName("Said")
            ->setSlug("jeannot-said")
            ->setEmail("jeannotsa@gmail.com")
            ->setPicture("https://via.placeholder.com/64")
            ->setHash($this->passwordEncoder->encodePassword(
            $adminUser,
           'password'
            ))
            ->setIntroduction("introduction de eric devolder")
            ->setDescription("description de eric devolder: Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.")
            ->addUserRole($adminRole);
            $manager->persist($adminUser);


        $slugify = new Slugify();
        $title="Titré de l'ànnonçe n°!";
        $slug=$slugify->slugify($title);
        
// creation utilisateur
for ($u=1; $u <=5; $u++) { 

        $user=new User();
        $user->setFirstName("prénom$u")
            ->setLastName("nom$u")
            ->setSlug("prenom$u-nom$u")
            ->setEmail("test$u@test.fr")
            ->setPicture("https://via.placeholder.com/64")
            ->setHash($this->passwordEncoder->encodePassword(
            $user,
           'password'
            ))
            ->setIntroduction("introduction de prenom$u nom$u")
            ->setDescription("description de prenom$u nom$u: Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");

            $manager->persist($user);

        // creation annonces
    	for ($i=1; $i <=mt_rand(1,5) ; $i++) { 

    		$ad = new Ad();


    		$ad->setTitle("Titre de l'annonce n° $i")
    			-> setSlug("$slug.$i")
    			-> setCoverImage("https://via.placeholder.com/300")
    			-> setIntroduction("<p>Introduction de l'<b>annonce $i</b></p>")
    			-> setContent("<p>Description de l'<b>annonce $i</b></p>")
    			-> setPrice(mt_rand(40,150))
    			-> setRooms(mt_rand(1,5))
                ->setAuthor($user);


            // ajout d'images à une annonce
                for ($j=0; $j < mt_rand(2,5) ; $j++) { 
                   
                    $image = new Image();
                    $image->setUrl("https://via.placeholder.com/300");
                   $image->setCaption("Légende de l'image $j");
                    $image->setAd($ad);

                    $manager->persist($image);


                }



    		$manager->persist($ad);
			$manager->flush();

			$slug2=$ad->getSlug().'_'.$ad->getId();
			$ad->setSlug($slug2);

			$manager->persist($ad);

            // creation réservations
            for ($k=0;$k<mt_rand(0,5);$k++)
                {
                    $booking= new Booking();

                    $datedebut=(new \DateTime("+ 5 days"));
                    $datedebut->setTime(0,0,0); // on se met à 00H00

                    $booking->setStartDate($datedebut);

                    $datefin=(new \DateTime("+ 15 days"));
                    $datefin->setTime(0,0,0);

                      $booking->setEndDate($datefin)
                      ->setCreatedAt(new \DateTime())
                      ->setAmount($ad->getPrice()*10)
                      ->setComment("Commentaire pour votre hote")
                      ->setAd($ad)
                      ->setBooker($user);

                      $manager->persist($booking);

                      //commentaires
                      if(mt_rand(0,1))
                      {
                        $comment = new Comment();
                        $comment->setCreatedAt(new \DateTime())
                                    ->setRating(mt_rand(0,5))
                                    ->setContent("commentaire de fin de reservation numero $k")
                                    ->setAd($ad)
                                    ->setAuteur($user)
                            ;
                             $manager->persist($comment);
                      }


                }






			$manager->flush();

    	}

}

        
    }
}
