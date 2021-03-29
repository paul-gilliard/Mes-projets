<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
            $captcha = $_POST["g-recaptcha-response"];
            $secretkey = "6LfJYTAaAAAAADgk7o8hVWTvxA3wwWPDuf0nVzKh";
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . urlencode($secretkey) . "&response=" . urlencode($captcha) . " ";
            $response = file_get_contents($url);
            $responsekey = json_decode($response, TRUE);
            // encode the plain password
            if ($responsekey['success'] && $form->isValid()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $user->setActuelStockage($user->getFormule() * 100000);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                // do anything else you need here, like send an email


                return $this->redirectToRoute('connection');
            }
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
