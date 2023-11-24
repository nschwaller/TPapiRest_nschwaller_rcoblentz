<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Session\Session;


class ApiController extends AbstractController
{
    #[Route('/api-ouverte-ent-liste', name: '/api_ouverte_ent_liste')]
    public function api_ouverte_ent_liste(Request $request): Response
    {
        // Vérifier le verbe HTTP
        if ($request->getMethod() !== 'GET') {
            return new Response('Méthode non autorisée', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/' . '/download';

        // Création d'une instance Finder pour rechercher les fichiers dans le dossier public
        $finder = new Finder();
        $finder->files()->in($publicDirectory);

        $listDownload = [];

        // Parcours des fichiers trouvés
        foreach ($finder as $file) {
            // Récupération du nom du fichier
            $fileName = $file->getFilename();

            // Récupération du numéro de titre (exemple : si le fichier est "123.txt", on extrait "123")
            $titleNumber = preg_replace('/\D/', '', $fileName);

            // Ajout du numéro de titre à la liste si trouvé
            if (!empty($titleNumber)) {
                $listDownload[] = $titleNumber;
            }
        }

        // Compte le nombre d'entrées
        $count = count($listDownload);

        // Vérification du format demandé dans le header Accept
        $acceptHeader = $request->headers->get('Accept');

        if ($count > 0) {
            if ($acceptHeader === 'application/json') {
                // Retourner la liste au format JSON
                return new Response($this->json($listDownload), Response::HTTP_OK);
            } elseif ($acceptHeader === 'text/csv') {
                // Générer le contenu CSV
                $csvContent = implode("\n", $listDownload);

                // Retourner une réponse avec le contenu CSV
                $response = new Response($csvContent);
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', 'attachment; filename="entreprises.csv"');
                return new Response($response, Response::HTTP_OK);
            } else {
                // Format non pris en charge
                return new Response('Format non pris en charge', Response::HTTP_NOT_ACCEPTABLE);
            }
        } else {
            return new Response('Aucune entreprise enregistrée');
        }
    }

    #[Route('/api-ouverte-ent-liste/{id}', name: '/api_ouverte_ent_liste_siren', methods: ['GET'])]
    public function api_ouverte_ent_liste_siren(Request $request, $id): Response
    {
        // Vérification du verbe HTTP
        if ($request->getMethod() !== 'GET') {
            return new Response('Méthode non autorisée', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/download';

        // Chemin complet du fichier basé sur l'ID reçu
        $filePath = $publicDirectory . '/' . $id . '.json';

        // Vérifier si le fichier existe
        if (file_exists($filePath)) {
            // Lire le contenu du fichier
            $fileContent = file_get_contents($filePath);
    
            // Retourner le contenu au format JSON
            return new Response($fileContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
         
        } else {
            // Aucune entreprise avec ce SIREN
            return new Response('Aucune entreprise avec ce SIREN', Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/api-ouverte-entreprise', name: 'api_ouverte_entreprise', methods: ['POST'])]
    public function api_ouverte_entreprise(Request $request): Response
    {
        // Vérification du verbe HTTP
        if ($request->getMethod() !== 'POST') {
            return new Response('Méthode non autorisée', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $data = json_decode($request->getContent(), true);

        // Vérification si les données sont présentes et valides
        if (
            !$data
            || !isset($data['Siren'], $data['Raison_sociale'], $data['Adresse'], $data['Adresse']['Num'], $data['Adresse']['Voie'], $data['Adresse']['Code_postale'], $data['Adresse']['Ville'], $data['Adresse']['GPS'], $data['Adresse']['GPS']['Latitude'], $data['Adresse']['GPS']['Longitude'])
            || !ctype_digit($data['Siren']) || strlen($data['Siren']) !== 9
            || empty($data['Raison_sociale'])
            || empty($data['Adresse']['Ville'])
            || !ctype_digit($data['Adresse']['Code_postale']) || strlen($data['Adresse']['Code_postale']) !== 5
            || !is_numeric($data['Adresse']['GPS']['Latitude']) || !is_numeric($data['Adresse']['GPS']['Longitude'])
        ) {
            // Format JSON invalide ou données manquantes
            return new Response('Format JSON invalide ou données manquantes', Response::HTTP_BAD_REQUEST);
        }

        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public/download';

        // Chemin du fichier basé sur le Siren
        $filePath = $publicDirectory . '/' . $data['Siren'] . '.txt';

        // Vérifier si le fichier existe déjà
        if (file_exists($filePath)) {
            // Entreprise existe déjà
            return new Response('Entreprise existe déjà', Response::HTTP_CONFLICT);
        }

        // Création du contenu JSON
        $jsonContent = json_encode($data);

        // Écriture du contenu dans un fichier
        file_put_contents($filePath, $jsonContent);

        // Retourner une réponse avec l'URL vers la nouvelle ressource
        $url = $request->getSchemeAndHttpHost() . '/download/' . $data['Siren'];
        return new Response('Entreprise créée', Response::HTTP_CREATED, ['Location' => $url]);
    }



}
