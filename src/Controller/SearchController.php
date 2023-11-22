<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Controller\EntrepriseUrssafController; 

class SearchController extends AbstractController
{

    #[Route('/redirect_urssaf', name: 'redirect_urssaf')]
    public function redirect_urssaf(Request $request): Response
    {    
        return $this->redirectToRoute('calcul_salaire', array(), 301);
    }

    #[Route('/download/{id}', name: 'download_id')]
    public function download_id(Request $request, $id): Response
    {    
        // Effectuez une requête GET vers l'API externe avec le nom saisi
        $apiUrl = 'https://recherche-entreprises.api.gouv.fr/search?q=' . $id; //On tape dans l'url de l'api en lui passant en paramettre $id correspondant au siren
        $client = HttpClient::create();
        $response = $client->request('GET', $apiUrl);
        
        //On formate la reponse en array et on y accede (car sous la forme d'un array dans un array)
        $content = $response->toArray();
        $response = $content['results'][0]; 

        // Écrit le contenu dans un fichier texte
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . 'download/' . $id . '.json';
        file_put_contents($filePath, print_r(json_encode($response), true));

        // Crée une réponse pour le téléchargement
        $file = new Response();
        $file->headers->set('Content-Type', 'application/json');
        $file->headers->set('Content-Disposition', 'attachment; filename="' . $id . '.txt"');
        $file->setContent(file_get_contents($filePath));

        return $file;
    }


    #[Route('/search/{id}', name: 'compagny_search_id')]
    public function searchid(Request $request, $id): Response
    {    
        // Effectuez une requête GET vers l'API externe avec le nom saisi
        $apiUrl = 'https://recherche-entreprises.api.gouv.fr/search?q=' . $id; //On tape dans l'url de l'api en lui passant en paramettre $id correspondant au siren
        $client = HttpClient::create();
        $response = $client->request('GET', $apiUrl);
        
        //On formate la reponse en array et on y accede (car sous la forme d'un array dans un array)
        $content = $response->toArray();
        $response = $content['results'][0]; 

        //Ajoute le Siren en sessionStorage
        $session = new Session();
        $session->start();
        $session->set('id',$id);

        return $this->render('results/index.html.twig', [
            'results' =>  $response , //On envoie l'array interresant a TWIG
        ]);
    }


    #[Route('/search', name: 'compagny_search')]
    public function search(Request $request): Response
    {    
        $results = '' ; //On initialise les résultats afin de ne jamais envoyé une valeur null a TWIG

        //On créé un forme a la volée
        $form = $this->createFormBuilder()
            ->add('name', TextareaType::class, [
                'attr' => [
                    'placeholder' => "nom de l'entreprise"
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => 'chercher'
            ])
            ->getForm();
        ;  
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
 
            $data = $form->getData();

            // Effectuez une requête GET vers l'API externe avec le nom saisi
            $apiUrl = 'https://recherche-entreprises.api.gouv.fr/search?q=' . $data['name']; 
            $client = HttpClient::create();
            $response = $client->request('GET', $apiUrl);

            // Retourne le resultat formatté
            $content = $response->toArray();
            $results = $content['results'] ;
        }
    
        return $this->render('search/index.html.twig', [
            'searchFormType' => $form -> createView() , //Retourne le formulaire a TWIG
            'results' =>  $results , //Retourne les resultats a TWIG
        ]);

    
    }

}
