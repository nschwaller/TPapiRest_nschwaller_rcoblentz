<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;


class ApiProtegeController extends AbstractController
{
    #[Route('/update-entreprise', name: 'update_entreprise', methods: ['PATCH'])]
    public function updateEntreprise(Request $request): Response
    {
        // Vérifier le verbe HTTP
        if ($request->getMethod() !== 'PATCH') {
            return new Response('Méthode non autorisée', Response::HTTP_METHOD_NOT_ALLOWED);
        }
    
        // Vérification de l'authentification basique
        if (!$this->isAuthenticated($request)) {
            return new Response('Non authentifié', Response::HTTP_UNAUTHORIZED);
        }
    
        // Récupérer les données du corps de la requête
        $body = json_decode($request->getContent(), true);
    
        // Vérifier si la clé "siren" est présente dans le corps de la requête
        if (!isset($body['siren'])) {
            return new Response('SIREN manquant dans le corps de la requête', Response::HTTP_BAD_REQUEST);
        }
    
        // Utiliser le siren comme ID
        $siren = $body['siren'];
    
        // Chemin vers le fichier JSON correspondant à l'ID SIREN
        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/download';
        $jsonFilePath = $publicDirectory . '/' . $siren . '.json';

    
        // Vérifier si le fichier JSON existe
        if (!file_exists($jsonFilePath)) {
            return new Response('Aucune entreprise avec ce SIREN'  , Response::HTTP_NOT_FOUND);
        }
    
        // Charger le contenu du fichier JSON
        $jsonData = file_get_contents($jsonFilePath);
    
        // Vérifier si le JSON est valide
        if ($jsonData === false || !($data = json_decode($jsonData, true))) {
            return new Response('Format JSON invalide', Response::HTTP_BAD_REQUEST);
        }
    
        // Parcourir le corps de la requête pour mettre à jour les données
        foreach ($body as $key => $value) {
            // Ne pas mettre à jour la valeur SIREN
            if ($key !== 'siren') {
                $data[$key] = $value;
            }
        }
    
        // Convertir le tableau associatif en JSON
        $newJsonData = json_encode($data, JSON_PRETTY_PRINT);
    
        // Écrire les modifications dans le fichier JSON
        file_put_contents($jsonFilePath, $newJsonData);
    
        // Répondre avec succès
        return new Response('Entreprise modifiée avec succès', Response::HTTP_OK );
    }


    #[Route('/delete-entreprise', name: 'delete_entreprise', methods: ['DELETE'])]
    public function deleteEntreprise(Request $request): Response
{
    // Vérifier le verbe HTTP
    if ($request->getMethod() !== 'DELETE') {
        return new Response('Méthode non autorisée', Response::HTTP_METHOD_NOT_ALLOWED);
    }

    // Vérification de l'authentification basique
    if (!$this->isAuthenticated($request)) {
        return new Response('Non authentifié', Response::HTTP_UNAUTHORIZED);
    }

    // Récupérer les données du corps de la requête
    $body = json_decode($request->getContent(), true);

    // Vérifier si la clé "SIREN" est présente dans le corps de la requête
    if (!isset($body['siren'])) {
        return new Response('SIREN manquant dans le corps de la requête', Response::HTTP_BAD_REQUEST);
    }

    // Utiliser le SIREN pour localiser le fichier JSON correspondant à l'entreprise
    $siren = $body['siren'];
    $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/download';
        $jsonFilePath = $publicDirectory . '/' . $siren . '.json';

    // Vérifier si le fichier JSON existe
    if (!file_exists($jsonFilePath)) {
        return new Response('Aucune entreprise avec ce SIREN', Response::HTTP_NOT_FOUND);
    }

    // Supprimer le fichier correspondant à l'entreprise
    unlink($jsonFilePath);

    // Répondre avec succès
    return new Response('Entreprise supprimée avec succès');
    }



    // Méthode pour vérifier l'authentification basique
    private function isAuthenticated(Request $request): bool
    {
        // Vérification de l'authentification basique (simulée ici)
        // Vérifiez les identifiants de manière sécurisée (ne pas stocker en dur en production)
        $user = 'username';
        $password = 'password';

        $authorizationHeader = $request->headers->get('Authorization');
        if ($authorizationHeader && strpos($authorizationHeader, 'Basic ') === 0) {
            $base64Credentials = substr($authorizationHeader, 6);
            $credentials = base64_decode($base64Credentials);
            [$enteredUser, $enteredPassword] = explode(':', $credentials);

            return ($enteredUser === $user && $enteredPassword === $password);
        }

        return false;
    }

}
