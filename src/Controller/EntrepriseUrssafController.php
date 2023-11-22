<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Form\UrssafFormType;

class EntrepriseUrssafController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    #[Route('/calcul-salaire', name: 'calcul_salaire')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(UrssafFormType::class);
        $form->handleRequest($request);
    
        $resultats = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $resultats = $this->callApi($data['salaire_brut'], $data['contrat']);
        }
    
        return $this->render('entreprise_urssaf/index.html.twig', [
            'form' => $form->createView(),
            'resultats' => $resultats,
        ]);
    }

    public function callApi($salaireBrut, $typeContrat)
    {
        try {
            $response = $this->client->request('POST', 'https://mon-entreprise.urssaf.fr/api/v1/evaluate', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    // 'Authorization' => 'Bearer VOTRE_TOKEN_ICI', // Si un token est nécessaire, décommentez cette ligne et remplacez VOTRE_TOKEN_ICI par votre token.
                ],
                'json' => [
                    'situation' => [
                        'salarié . contrat . salaire brut' => [
                            'valeur' => $salaireBrut,
                            'unité' => '€ / mois'
                        ],
                        'salarié . contrat' => "'$typeContrat'" 
                    ],
                    'expressions' => [
                        'salarié . rémunération . net . à payer avant impôt'
                    ]
                ]
            ]);

            $responseData = $response->toArray();
            // Enregistrez la réponse complète pour le débogage
            file_put_contents('api_response.log', print_r($responseData, true));

            // Assurez-vous que la réponse contient les données attendues
            if (isset($responseData['evaluate'][0]['nodeValue'])) {
                // Construisez le tableau de résultats attendu par votre template
                $resultats = [
                    'salaire_brut' => $salaireBrut,
                    'contrat' => $typeContrat,
                    'salaire_net_avant_impot' => $responseData['evaluate'][0]['nodeValue'],
                    // 'cout_employeur' => ... // Ajoutez cette ligne si vous avez cette information
                ];
            } else {
                // Gérez le cas où la réponse ne contient pas les données attendues
                $resultats = [
                    'situationError' => 'Les données attendues ne sont pas présentes dans la réponse de l\'API.'
                ];
            }

            return $resultats;
        } catch (\Throwable $e) {
            // Enregistrez l'erreur pour le débogage
            file_put_contents('api_error.log', $e->getMessage());

            return [
                'situationError' => 'Une erreur est survenue lors de l\'appel à l\'API.'
            ];
        }
    }
}