<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\augmentationQuotaForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Transport;
use Symfony\Bridge\Monolog\Handler\SwiftMailerHandler;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class AugmentationQuotaController extends AbstractController
{
    /**
     * @Route("/augmentation/quota", name="augmentation_quota")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $data[] = $this->getUser()->getFormule();
        $formUpgrade = $this->createForm(augmentationQuotaForm::class, $data);
        $formUpgrade->handleRequest($request);

        if ($formUpgrade->isSubmitted() && $formUpgrade->isValid()) {
            $formuleChoisi = floatval($formUpgrade->get('formule')->getData());
            $stockage=$this->getUser()->getActuelStockage();
            $formuleActuelle=floatval($this->getUser()->getFormule());
            $this->getUser()->setFormule($formuleChoisi);
            $this->getUser()->setActuelStockage($stockage+(($formuleChoisi-$formuleActuelle)*100000));
            $em = $this->getDoctrine()->getManager();
            $em->persist($this->getUser());
            $em->flush();

            return $this->render('mail/index.html.twig',[
                "formuleChoisi" => $formuleChoisi,
                'formuleActuelle' => $formuleActuelle

            ]);
        }
        return $this->render('augmentation_quota/index.html.twig', [
            'formUpgrade' => $formUpgrade->createView(),

        ]);
    }
}
