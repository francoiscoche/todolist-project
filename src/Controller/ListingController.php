<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Listing;
use Symfony\Component\Routing\Annotation\Route; // Pour crèer les routes #Route...
use Doctrine\Persistence\ManagerRegistry; // Pour faire le lien avec le Entity (BDD)
use Symfony\Component\HttpFoundation\Request; // Pour récuperer les data des formulaires
use Doctrine\DBAL\Exception\UniqueConstraintViolationException; // Pour eviter le retour erreur de doublons dans la base de données


#[Route('/', name: 'listing_')]
class ListingController extends AbstractController
{

    // Method pour l'affichage des tasks, on renvoi en front la liste des tasks
    #[Route('/{taskId}', name: 'show', requirements:["taskId"=>"\d+"])]
    public function list(ManagerRegistry $entityManager, $taskId = null)
    {
        $listingTasks = $entityManager->getRepository(Listing::class)->findAll();

        if (!empty($taskId)) {
            $currentTask = $entityManager->getRepository(Listing::class)->find($taskId);
        }

        if (empty($currentTask)) {
            $currentTask = current($listingTasks);
        }

        return $this->render("dashboard.html.twig", ["listingTasks" => $listingTasks, 'currentTask' => $currentTask]);
    }



    // Method pour la suppresion d'une task
    #[Route('/delete/{taskId}', name:"delete")]
    public function deletdddde(ManagerRegistry $doctrine, $taskId)
    {
        $entityManager = $doctrine->getManager();
        // Avec l'ID passé en parametre de la function delete on récuperer les infos depuis la BDD
        $task = $entityManager->getRepository(Listing::class)->find($taskId);
        if(empty($task)) {
            $this->addFlash(
                type: "warning",
                message: "Impossible de supprimer la liste"
            );
        } else {
            $entityManager->remove($task); // On lui demande de persister, de sauvegarder
            $entityManager->flush(); // Pour envoyer l'ensemble des requetes.

            $name = $task->getName();

            $this->addFlash(
                type: "success",
                message: "La liste « $name » a été supprimée avec succes"
            );
        }

        // return new Response("OK");
        return $this->redirectToRoute('listing_show'); // On redirige sur la meme page avec "redirectToRoute"
    }


    // Method pour la création de tasks.
    #[Route('/create', name: "create", methods: ['POST'])]
    public function create(ManagerRegistry $doctrine, Request $request)
    {

        $entityManager = $doctrine->getManager();
        $name = $request->get('name');

        if (empty($name)) {
            $this->addFlash(
                "warning",
                "Un nom de liste est obligatoire"
            );
            return $this->redirectToRoute('listing_show');
        }

        $listing = new Listing();
        $listing->setName($name);

        try {
            $entityManager->persist($listing);
            $entityManager->flush();

            $this->addFlash(
                "success",
                "La liste « $name » a été créée avec succès"
            );
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash(
                "warning",
                "Impossible de créer la liste « $name »"
            );
        }

        return $this->redirectToRoute('listing_show');
    }
}