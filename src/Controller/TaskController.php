<?php

namespace App\Controller;

use App\Entity\Listing;
use App\Entity\Task;
use App\Form\TaskType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

#[Route('({taskId}/task', name:"task_", requirements:["taskId"=>"\d+"])] // d+ pour digit
class TaskController extends AbstractController {

    // Ici on vient créer le formulaire de la subtask et l'enregistrer dans la base de données
    #[Route('/new', name:"create")]
    public function create(Request $request, ManagerRegistry $doctrine, $taskId)
    {
        $entityManager = $doctrine->getManager();
        $listing = $entityManager->getRepository(Listing::class)->find($taskId); // On vient récupérer la tache que l'on est en train de modifier (non pas la subtask)

        // On fait appel au formulaire que l'on a crée dans Form/TaskType.php
        $task = new Task();
        $task->setListing($listing); // On lui précise dans quel tache la subtask va etre créer

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request); // pour récuperer les requetes qui viennent du formulaire
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            // Si cela fonctionne on redirige vers le listing en cours, celui que l'on est en train d'ajouter une tache
            return $this->redirectToRoute('listing_show', ['taskId'=> $taskId]);
        }


        // On vient rendre le formulaire dans un nouveau template
        return $this->render("task.html.twig", ['form' => $form->createView()]);
    }


    #[Route('/{subTaskId}/edit', name:"edit", requirements:["subTaskId"=>"\d+"])]
    public function update(Request $request, ManagerRegistry $doctrine, $taskId, $subTaskId) {

        $entityManager = $doctrine->getManager();

        $subTask = $entityManager->getRepository(Task::class)->find($subTaskId);

        if (empty($subTask)) {
            $this->addFlash(
                'warning',
                "Impossible de modifier la tâche"
            );
            return $this->redirectToRoute('listing_show', ['taskId' => $taskId]);
        }


        $name = $subTask->getName();

        $form = $this->createForm(TaskType::class, $subTask);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                "La tâche « $name » a été modifiée avec succès"
            );

            return $this->redirectToRoute('listing_show', ['taskId' => $taskId]);
        }

        return $this->render('task.html.twig', ['form' => $form->createView()]);

    }


    #[Route('/{subTaskId}/delete', name:"delete")]
    public function delete(ManagerRegistry $doctrine, $taskId, $subTaskId)
    {
        $entityManager = $doctrine->getManager();

        $subTask = $entityManager->getRepository(Task::class)->find($subTaskId);

        if (empty($subTask)) {
            $this->addFlash(
                'warning',
                "Impossible de supprimer la tâche"
            );
            return $this->redirectToRoute('listing_show', ['taskId' => $taskId]);
        }



        $entityManager->remove($subTask); // On lui demande de persister, de sauvegarder
        $entityManager->flush(); // Pour envoyer l'ensemble des requetes.

        $name = $subTask->getName();

        $this->addFlash(
            type: "success",
            message: "La liste « $name » a été supprimée avec succes"
        );


        // return new Response("OK");
        return $this->redirectToRoute('listing_show', ['taskId' => $taskId]); // On redirige sur la meme page avec "redirectToRoute"
    }


}