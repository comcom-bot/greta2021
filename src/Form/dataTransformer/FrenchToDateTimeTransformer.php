<?php

namespace App\Form\dataTransformer;

use App\Entity\Issue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface
{
	 //pour passer les données aux formulaire (ca vient de la base de donnée)
	  public function transform($date)
    {
      if($date == null)
      {
      	return '';
      }

      return $date->format('d/m/Y');
    }
    // //pour recupérer les données du formulaire les transformer en datetime
      public function reverseTransform($frenchDate)
    {
       $date =  \DateTime::createFromFormat('d/m/Y',$frenchDate);
    	$date->setTime(0,0,0);
       return $date;
    }
}
?>