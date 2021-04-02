<?php

namespace App\Form;

use App\Entity\Booking;

use App\Form\dataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class BookingType extends AbstractType
{
    
    private $transformer;

    public function __construct(FrenchToDateTimeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate',TextType::class)
            //ou on peut aussi utiliser les  
            //->add('startDate',TextType::class,['widget'=>'single_text','html5'=false])
            //on peut alors se passer de transformer
            ->add('endDate',TextType::class)
            //->add('startDate',DateType::class,['widget'=>'single_text'])
            //->add('endDate',DateType::class,['widget'=>'single_text'])
            ->add('comment')
         
        ;
        $builder->get('startDate')
            ->addModelTransformer($this->transformer);

        $builder->get('endDate')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
