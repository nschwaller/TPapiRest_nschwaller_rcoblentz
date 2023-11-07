<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;


class SearchController extends AbstractController
{

    #[Route('/test/{id}', name: 'compagny_search_id')]
    public function searchid(Request $request, $id): Response
    {    
        // Effectuez une requête GET vers l'API externe avec le nom saisi
        $apiUrl = 'https://recherche-entreprises.api.gouv.fr/search?q=' . $id; //On tape dans l'url de l'api en lui passant en paramettre $id correspondant au siren
        $client = HttpClient::create();
        $response = $client->request('GET', $apiUrl);
        
        //On formate la reponse en array et on y accede (car sous la forme d'un array dans un array)
        $content = $response->toArray();
        $response = $content['results'][0]; 
       
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
