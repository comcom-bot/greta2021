<?php 
namespace App\Services;

use App\Entity\ImageUpload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class ImagesUploadService extends AbstractController
{


	public function upload($ad,$manager){


				    // images telechargées		
			foreach ($ad->file as $file) {
				
				//$position_point = strpos($file->getClientOriginalName(),'.');
				//$original_name = substr($file->getClientOriginalName(),0,$position_point);

				$original_name = preg_replace('#\.(png|jpg|jpeg)$#','',$file->getClientOriginalName());

				$filename = uniqid().'.'.$file->guessExtension();


				$upload = new ImageUpload();
				$upload->setName($original_name)
					   ->setUrl('/uploads/'.$filename)
					   ->setAd($ad);
				$manager->persist($upload);


				// deplacement du fichier dans le dossier uploads
				$file->move($this->getParameter('directory_files'),
                        $filename);

				


			}





	}




}