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
        $apiUrl = 'https://recherche-entreprises.api.gouv.fr/search?q=' . $id; // Adapt to your API
        $client = HttpClient::create();
        $response = $client->request('GET', $apiUrl);
        //$results = json_decode($results, true);
        
        $content = $response->toArray();
       
        $response = $content['results'][0]; 
       
        return $this->render('results/index.html.twig', [
            'results' =>  $response ,
        ]);
    }


    #[Route('/search', name: 'compagny_search')]
    public function search(Request $request): Response
    {    
        $results = '' ; 

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
            $apiUrl = 'https://recherche-entreprises.api.gouv.fr/search?q=' . $data['name']; // Adapt to your API
            $client = HttpClient::create();
            $response = $client->request('GET', $apiUrl);
            //$results = json_decode($results, true);
            
            $content = $response->toArray();
            $results = $content['results'] ;
            //  dd($content);
        }
    
        return $this->render('search/index.html.twig', [
            'searchFormType' => $form -> createView() ,
            'results' =>  $results ,
        ]);
    
    }

}
