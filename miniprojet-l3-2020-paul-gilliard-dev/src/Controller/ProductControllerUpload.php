<?php


// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\mkdir;
use App\Entity\Product;
use App\Form\ProductType;
use App\Form\mkdirFormType;
use Symfony\Component\PropertyAccess\PropertyAccess;
use phpDocumentor\Reflection\Types\String_;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductControllerUpload extends AbstractController
{
   
    protected $cheminDossier;
    /**
     * @Route("/product/new/it/{cheminDossier}", name="app_product_upload")
     */

    public function new(Request $request, SluggerInterface $slugger,String $cheminDossier)
    {
        if($cheminDossier=='racineDirectory'){
            $cheminDossierUse='';}
        else{
            $cheminDossierUse=substr($cheminDossier,0,-9);
            $cheminDossierUse = str_replace("|", "/", $cheminDossierUse);
        }

        $nomUser = $this->getUser()->getUsername();
        $cheminDossierUser = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;

        $dir = opendir($cheminDossierUser) or die('Erreur de listage : le répertoire n\'existe pas');





        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);




            if ($form->isSubmitted() && $form->isValid()) {
                /** @var UploadedFile $brochureFile */
                $brochureFile = $form->get('brochure')->getData();

                if ($brochureFile) {

                    $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                    // Move the file to the directory where brochures are stored


                    $user = $this->getUser();
                    $stockage = $this->getUser()->getActuelStockage();

                    if ($stockage == 0) {
                        $stockageInitial = floatval($this->getUser()->getFormule() * 1000000);

                        $this->getUser()->setActuelStockage($stockageInitial);
                    }

                    $stockageActuel = $this->getUser()->getActuelStockage();


                    if ($stockageActuel - $brochureFile->getSize() < 0) {

                        return $this->forward('App\Controller\AugmentationQuotaController::index');

                    } else {
                        try {


                            $Debut = $this->getParameter('brochures_directory');
                            $nomUser = $this->getUser()->getUsername();
                            $Debut = $Debut . '/' . $nomUser . '/' . $cheminDossierUse;
                            $brochureFile->move(
                                $Debut,
                                $newFilename
                            );
                        } catch (FileException $e) {
                            // ... handle exception if something happens during file upload
                        }


                        // updates the 'brochureFilename' property to store the PDF file name
                        // instead of its contents
                        $product->setBrochureFilename($newFilename);

                        $this->getUser()->setActuelStockage($stockageActuel - $brochureFile->getSize());
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();

                    }
                }

                // ... persist the $product variable or any other work

                return $this->forward('App\Controller\ProductController::new');
            } // L'upload de fichier



        //$nameDir = $this->getParameter('kernel.project_dir').'/public';
        //$nameDir = $this->getParameter('kernel.project_dir').'/public';
        $nomUser = $this->getUser()->getUsername();
        $cheminDossierUser = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;

        if (file_exists($cheminDossierUser)) {
        }
        else
        {
            mkdir($cheminDossierUser);
        }

        $dir = opendir($cheminDossierUser) or die('Erreur de listage : le répertoire n\'existe pas');
        $files = array();


        $dir_iterator = new RecursiveDirectoryIterator($cheminDossierUser,0,false);
        $iterator = new RecursiveIteratorIterator($dir_iterator,1,0);
        //  new RecursiveDirectoryIterator()

        //$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($nameDir), RecursiveIteratorIterator::SELF_FIRST);
        $Folder=[];
        $Folder[]=["racine"."Directory"];
        $Files=[];
        foreach ($iterator as $file) {

            $test = $file->getFilename();
            if($test != '..' && $test != '.' ) {
                if(is_dir($file)) {
                    $test = str_replace("/", "|", $file);
                    $Folder[] =  [$test."Directory"];
                }
                else{
                    $test = $file->getPathname();
                    $aEnlever= $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;
                    $test = str_replace($aEnlever, "", $test);
                    $test = str_replace("/", "|", $test);
                    $Files[] = $test."File";
                }
            }
        }

        // var_dump($Folder);
        //var_dump($Files);

        foreach ($Files as $file)
        {

            for ($i=0;$i<count($Folder);$i++)
            {
                $chaineATest=substr($Folder[$i][0],0,-9);
                $nombreBarre = substr_count($file,'|');
                //var_dump($nombreBarre);
                $tabSeparate = explode("|", $file);
                //var_dump($tabSeparate);
                if (count($tabSeparate)>2)
                {
                    $file2 = $tabSeparate[$nombreBarre - 1];
                }
                else{
                    $file2='chaineImPosSibleAtRouver';
                    $FichierDeLaRacine[0][]=$file;
                }



                $nombreBarre = substr_count($chaineATest,'|');
                $tabSeparate = explode("|", $chaineATest);
                $chaineATest=$tabSeparate[$nombreBarre];

                // var_dump($chaineATest);

                if (str_contains($file2,$chaineATest))
                {

                    $Folder[$i][]=$file;
                }
            }


        }
        if (isset( $FichierDeLaRacine ) ) {
            $FichierDeLaRacine = array_unique($FichierDeLaRacine);
            for ($i = 0; $i < count($FichierDeLaRacine); $i++) {
                $Folder[0][] = $FichierDeLaRacine[$i][0];
            }
        }

        for ($i=0;$i<count($Folder);$i++)
        {

            $file=$Folder[$i][0];

            $nomUser= $this->getUser()->getUsername();
            $chaineAvirer = $this->getParameter('kernel.project_dir') . '/public/'. $nomUser;
            $chaineAvirer = str_replace("/", "|", $chaineAvirer);
            $nomDossierPropre = str_replace($chaineAvirer,"",$file);
            $Folder[$i][0]=$nomDossierPropre;

        }

        for ($i=0;$i<count($Folder);$i++)
        {
            $tableauDeBase = $Folder[$i];
            $tableauAsso=[];

            foreach ($tableauDeBase as $file)
            {
                if(str_contains($file,"Directory"))
                {

                    if($file=='racineDirectory')
                    {
                        $nomTransition='';
                    }
                    else {

                        $nomTransition = str_replace("|", "/", $file);
                        $nomTransition = substr($nomTransition, 0, -9);
                    }

                    $compteur = 0;
                    $element = $this->getParameter('kernel.project_dir') . '/public/'. $nomUser . $nomTransition;
                    $dir_iterator = new RecursiveDirectoryIterator($element,0,true);
                    $iterator = new RecursiveIteratorIterator($dir_iterator,1,0);
                    foreach ($iterator as $key=>$value)
                    {

                        $test = str_replace("$nomUser", "|", $key);

                        if (!str_contains($test,"/."))

                            $compteur++;

                    }


                    $tableauAsso[$file] = $compteur ;
                }
                else
                {
                    $filePourDate = str_replace("|", "/", $file);
                    $filePourDate= substr($filePourDate,0,-4);

                    $tableauAsso[$file] = "Date et heure de l'upload : " . date ("F d Y H:i:s.", filemtime($this->getParameter('kernel.project_dir') . '/public/'. $nomUser . $filePourDate)) . " taille : ". filesize($this->getParameter('kernel.project_dir') . '/public/'. $nomUser . $filePourDate) . "KB";
                }
            }
            $Folder[$i]=$tableauAsso;
        }

       // var_dump($Folder);
        closedir($dir);



        $mkdir = new mkdir();
        $formMkdir = $this->createForm(mkdirFormType::class, $mkdir);
        $formMkdir->handleRequest($request);

        if ($formMkdir->isSubmitted() && $formMkdir->isValid()) {
            $nomDossierDonne = $formMkdir->get('nameDir')->getData();

            try {
                $Debut = $this->getParameter('brochures_directory');
                $nomUser = $this->getUser()->getUsername();
                $Debut = $Debut . '/' . $nomUser.'/'.$cheminDossierUse.'/'.$nomDossierDonne;
                mkdir($Debut);

                return $this->forward('App\Controller\ProductController::new');

            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

        }
        $AfficherAjoutFichier = true;
        $AfficherAjoutDossier = true;
        $stockageRestant = $this->getUser()->getActuelStockage();

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'cheminDossierUser' => $files,
            'filesPath'=> $Folder,
            'url'=> 'download/',
            'endroitAdding'=>$cheminDossier,
            'endroitAddingDossier'=>$cheminDossier,
            'formMkdir' => $formMkdir->createView(),
            'AfficherAjoutFichier' => $AfficherAjoutFichier,
            'AfficherAjoutDossier' => $AfficherAjoutDossier,
            'StockageRestant' => $stockageRestant


        ]);
    }

    /**
     * @return BinaryFileResponse
     * @Route("/product/new/it/download/{path}", name="app_product_downloaad")
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

}
