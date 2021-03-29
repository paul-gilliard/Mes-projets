<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\adminQuota;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(Request $request, UserRepository $userRepository) : Response
    {

        $allUser=$userRepository->findAll();
        $tableauDeTousLesFormes=[];
        for ($i=0;$i<count($allUser);$i++) {
            $user=$allUser[$i];
            $formUpgrade=[];
            $formUpgrade[] = $this->createForm(adminQuota::class, [$user->getFormule(),$user->getId()]);
            $tableauMemoireForm[]= $formUpgrade[0];
            $formUpgrade[0]->handleRequest($request);
            if ($formUpgrade[0]->isSubmitted() && $formUpgrade[0]->isValid()) {

                $formuleChoisi = floatval($formUpgrade[0]->get('formule')->getData());
                $idUser = floatval($formUpgrade[0]->get('token')->getData());
                $userQuiChange=$userRepository->findBy(['id'=>$idUser]);
                $stockage=$userQuiChange[0]->getActuelStockage();
                $formuleActuelle=floatval($userQuiChange[0]->getFormule());
                $userQuiChange[0]->setFormule($formuleChoisi);
                $userQuiChange[0]->setActuelStockage($stockage+(($formuleChoisi-$formuleActuelle)*100000));
                $em = $this->getDoctrine()->getManager();
                $em->persist($this->getUser());
                $em->flush();
                return $this->redirectToRoute('home');
            }
            $formUpgrade[] = $formUpgrade[0]->createView();
            array_shift($formUpgrade);
            $formUpgrade[]= $user;
            $tableauDeTousLesFormes[] = $formUpgrade;
        }



       /* $tableauMemoireForm[0]->handleRequest($request);
            if ($tableauMemoireForm[0]->isSubmitted() && $tableauMemoireForm[0]->isValid()) {
                print "azerty";
            }*/


     //   if ($formUpgrade->isSubmitted() && $formUpgrade->isValid()) {}





        return $this->render('home/index.html.twig', [
            'allUser'=>$allUser,
            'tableauDeTousLesFormes'=> $tableauDeTousLesFormes
        ]);
    }
}
