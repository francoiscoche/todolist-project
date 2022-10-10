<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/profile', name:"profile_")]
class ProfileController extends AbstractController
{

    #[Route('/', name: 'show')] // d+ pour digit
    public function show(ManagerRegistry $doctrine)
    {

        $userId = 1;
        $entityManager = $doctrine->getManager();
        $infosUser = $entityManager->getRepository(User::class)->find($userId);


        return $this->render("profile.html.twig", ["infosUser" => $infosUser]);
    }

    #[Route('/edit', name:"edit", methods: ['POST'])]
    public function update(Request $request, ManagerRegistry $doctrine)
    {

        $userId = 1;

        $entityManager = $doctrine->getManager();
        $getCity = $request->get('city');

        if (empty($getCity)) {
            $this->addFlash(
                "warning",
                "Un nom de ville est obligatoire"
            );
            return $this->redirectToRoute('profile_show');
        }

        $city = $entityManager->getRepository(User::class)->find($userId);

        if (empty($city)) {
            $this->addFlash(
                'warning',
                "Impossible de modifier la tâche"
            );
            return $this->redirectToRoute('profile_show');
        }

        $city->setLocality($getCity);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "La tâche « $getCity » a été modifiée avec succès"
        );

        return $this->redirectToRoute('profile_show');
    }

}