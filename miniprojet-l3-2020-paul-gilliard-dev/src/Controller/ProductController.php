<?php


// src/Controller/ProductController.php
namespace App\Controller;
use App\Entity\mkdir;
use App\Entity\Product;
use App\Form\ProductType;
use App\Form\mkdirFormType;
use App\Repository\UserRepository;
use App\Form\adminQuota;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/new/", name="app_product_new")
     */
    public function new(Request $request, SluggerInterface $slugger, UserRepository $userRepository)
    {

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);


        $stockage = $this->getUser()->getFormule();

        $nomUser = $this->getUser()->getUsername();
        $cheminDossierUser = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;

        if (file_exists($cheminDossierUser)) {
        } else {
            mkdir($cheminDossierUser);
        }

        $dir = opendir($cheminDossierUser) or die('Erreur de listage : le répertoire n\'existe pas');
        $files = array();


        $dir_iterator = new RecursiveDirectoryIterator($cheminDossierUser, 0, false);
        $iterator = new RecursiveIteratorIterator($dir_iterator, 1, 0);
        //  new RecursiveDirectoryIterator()

        //$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($nameDir), RecursiveIteratorIterator::SELF_FIRST);
        $Folder = [];
        $Folder[] = ["racine" . "Directory"];
        $Files = [];
        foreach ($iterator as $file) {

            $test = $file->getFilename();
            if ($test != '..' && $test != '.') {
                if (is_dir($file)) {
                    $test = str_replace("/", "|", $file);
                    $Folder[] = [$test . "Directory"];
                } else {
                    $test = $file->getPathname();
                    $aEnlever = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;
                    $test = str_replace($aEnlever, "", $test);
                    $test = str_replace("/", "|", $test);
                    $Files[] = $test . "File";
                }
            }
        }

        // var_dump($Folder);
        //var_dump($Files);

        foreach ($Files as $file) {

            for ($i = 0; $i < count($Folder); $i++) {
                $chaineATest = substr($Folder[$i][0], 0, -9);
                $nombreBarre = substr_count($file, '|');
                //var_dump($nombreBarre);
                $tabSeparate = explode("|", $file);
                //var_dump($tabSeparate);
                if (count($tabSeparate) > 2) {
                    $file2 = $tabSeparate[$nombreBarre - 1];
                } else {
                    $file2 = 'chaineImPosSibleAtRouver';
                    $FichierDeLaRacine[0][] = $file;
                }


                $nombreBarre = substr_count($chaineATest, '|');
                $tabSeparate = explode("|", $chaineATest);
                $chaineATest = $tabSeparate[$nombreBarre];

                // var_dump($chaineATest);

                if (str_contains($file2, $chaineATest)) {

                    $Folder[$i][] = $file;
                }
            }


        }
        if (isset($FichierDeLaRacine)) {
            $FichierDeLaRacine = array_unique($FichierDeLaRacine);
            for ($i = 0; $i < count($FichierDeLaRacine); $i++) {
                $Folder[0][] = $FichierDeLaRacine[$i][0];
            }
        }

        for ($i = 0; $i < count($Folder); $i++) {

            $file = $Folder[$i][0];

            $nomUser = $this->getUser()->getUsername();
            $chaineAvirer = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;
            $chaineAvirer = str_replace("/", "|", $chaineAvirer);
            $nomDossierPropre = str_replace($chaineAvirer, "", $file);
            $Folder[$i][0] = $nomDossierPropre;

        }

        for ($i = 0; $i < count($Folder); $i++) {
            $tableauDeBase = $Folder[$i];
            $tableauAsso = [];

            foreach ($tableauDeBase as $file) {
                if (str_contains($file, "Directory")) {

                    if ($file == 'racineDirectory') {
                        $nomTransition = '';
                    } else {

                        $nomTransition = str_replace("|", "/", $file);
                        $nomTransition = substr($nomTransition, 0, -9);
                    }

                    $compteur = 0;
                    $element = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser . $nomTransition;
                    $dir_iterator = new RecursiveDirectoryIterator($element, 0, true);
                    $iterator = new RecursiveIteratorIterator($dir_iterator, 1, 0);
                    foreach ($iterator as $key => $value) {

                        $test = str_replace("$nomUser", "|", $key);

                        if (!str_contains($test, "/."))

                            $compteur++;

                    }


                    $tableauAsso[$file] = $compteur;
                } else {
                    $filePourDate = str_replace("|", "/", $file);
                    $filePourDate = substr($filePourDate, 0, -4);

                    $tableauAsso[$file] = "Date et heure de l'upload : " . date("F d Y H:i:s.", filemtime($this->getParameter('kernel.project_dir') . '/public/' . $nomUser . $filePourDate)) . " taille : " . filesize($this->getParameter('kernel.project_dir') . '/public/' . $nomUser . $filePourDate) . "KB";
                }
            }
            $Folder[$i] = $tableauAsso;
        }

        closedir($dir);


        $mkdir = new mkdir();
        $formMkdir = $this->createForm(mkdirFormType::class, $mkdir);
        $formMkdir->handleRequest($request);

        $stockageRestant = $this->getUser()->getActuelStockage();



        $AfficherAjoutFichier = false;
        $AfficherAjoutDossier = false;
        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'cheminDossierUser' => $files,
            'filesPath'=> $Folder,
            'url'=> 'download/',
            'urlDossier'=>'',
            'endroitAdding'=>"racineDirectory",
            'endroitAddingDossier'=>"racineDirectory",
            'formMkdir' => $formMkdir->createView(),
            'test'=> $stockage,
            'AfficherAjoutFichier' => $AfficherAjoutFichier,
            'AfficherAjoutDossier' => $AfficherAjoutDossier,
            'StockageRestant' => $stockageRestant,
        ]);
    }

    /**
     * @return BinaryFileResponse
     * @Route("/product/new/download/{path}", name="app_product_download")
     */
    public function downloadAction($path)
    {
        //var_dump($path);
        $newPath = str_replace("|", "/", $path);
        //var_dump($newPath);
        $nomUser= $this->getUser()->getUsername();
        $cheminDossierUser = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser."/".$newPath;
        $cheminDossierUser=substr($cheminDossierUser,0,-4);
        //$path = $this->get('kernel')->getRootDir(). "/../downloads/";
        $file = $cheminDossierUser; // Path to the file on the server
        $response = new BinaryFileResponse($file);

        // Give the file a name:
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$path);

        return $response;
    }

  
    /**
     * @param $path
     * @Route ("/product/new/delete/{path}", name="app_product_delete")
     */
    public function deleteAction($path)
    {
        $newPath = str_replace("|", "/", $path);
        $newnewPath=substr($newPath,0,-4);
        $nomUser= $this->getUser()->getUsername();
        $cheminDuFichier = $this->getParameter('kernel.project_dir') . '/public/'. $nomUser . $newnewPath;

        $tailleFichier = filesize($cheminDuFichier);
        $stockage = $this->getUser()->getActuelStockage();
        $this->getUser()->setActuelStockage($stockage + $tailleFichier);
        $em = $this->getDoctrine()->getManager();
        $em->persist($this->getUser());
        $em->flush();


        unlink($cheminDuFichier);
        return $this->redirectToRoute('app_product_new');

        //return $response;
    }

    /**
     * @Route("/product/new/deleteDir/{path}", name="app_product_delete_dir")
     */
    public function deleteActionDir($path)
    {

        $newPath = str_replace("|", "/", $path);
        $newnewPath=substr($newPath,0,-9);
        $nomUser= $this->getUser()->getUsername();
        $user=$this->getUser();
        $cheminDuFichier = $this->getParameter('kernel.project_dir') . '/public/'. $nomUser . $newnewPath;
        
        $tailleFichierSupp=0;

        function deleteTree($dir,$user){
            foreach(glob($dir . "/*") as $element){
                if(is_dir($element)){
                    deleteTree($element,$user); // On rappel la fonction deleteTree
                    rmdir($element); // Une fois le dossier courant vidé, on le supprime
                } else { // Sinon c'est un fichier, on le supprime


                    $stockage = $user->getActuelStockage();
                    $tailleFichierSupp = filesize($element);
                    $user->setActuelStockage($stockage + $tailleFichierSupp);

                    unlink($element);

                }
                // On passe à l'élément suivant
            }

        }

        deleteTree($cheminDuFichier,$user);
        rmdir($cheminDuFichier); // Et on le supprime

        $em = $this->getDoctrine()->getManager();
        $em->persist($this->getUser());
        $em->flush();

        return $this->forward('App\Controller\ProductController::new');

        //return $response;
    }

    /**
     * @Route("/product/new/deleteUser/{path}/{id}", name="app_product_delete_user")
     */
    public function deleteUser($path,$id,UserRepository $userRepository)
    {


        $cheminDuFichier = $this->getParameter('kernel.project_dir') . '/public/' . $path;


            function deleteTree($dir){
                foreach(glob($dir . "/*") as $element){
                    if(is_dir($element)){
                        deleteTree($element); // On rappel la fonction deleteTree
                        rmdir($element); // Une fois le dossier courant vidé, on le supprime
                    } else { // Sinon c'est un fichier, on le supprime


                        unlink($element);

                    }
                    // On passe à l'élément suivant
                }

            }

        deleteTree($cheminDuFichier);
        rmdir($cheminDuFichier); // Et on le supprime

        $a=$userRepository->findBy(['id'=>$id]);
        $em = $this->getDoctrine()->getManager();
        $em->remove($a[0]);
        $em->persist($this->getUser());
        $em->flush();

        return $this->redirectToRoute('home');

        //return $response;
    }


}

