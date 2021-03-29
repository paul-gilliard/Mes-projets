<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConnectionController extends AbstractController
{
    /**
     * @Route("/a", name="connectiona")
     * @param Request $request
     * @return Response
     */
    public function CreationCompte(Request $request)
    {

       // $contrainte = new Regex(['pattern'=>'/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/','message'=>'Minimum eight characters, at least one letter and one number' ]);

        $builder = $this->createFormBuilder(array('csrf_protection' => false));

        $builder->add('email', EmailType::class)
            ->add('password', RepeatedType::class)
            ->add('boutonSubmit', SubmitType::class);


        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            #var_dump($builder);
            return $this->render('home/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
        }
        else
        {

            $infoRendu = $form->createView();
            return $this->render('connection/index.html.twig',[ 'infoForm' => $infoRendu
            ]);
        }
    }
    /**
     * @Route("/welcome",name="welcome")
     * @return Response
     */
    public function welcome (TranslatorInterface $translator)
    {
        return new Response($translator->trans("Welcome in the site"));
    }

}

