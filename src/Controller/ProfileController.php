<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CallApiForecastService;
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
    public function update(Request $request, ManagerRegistry $doctrine, CallApiForecastService $callApiForecastService)
    {

        $userId = 1;

        $entityManager = $doctrine->getManager();

        // Get CP from profile form
        $getCp = $request->get('cp');

        if (empty($getCp)) {
            $this->addFlash(
                "warning",
                "A postal code is mandatory"
            );
            return $this->redirectToRoute('profile_show');
        }


        // Get city name and insee number from CP
        $infoCity = $callApiForecastService->getInfosCitiesByCP($getCp);

        $locality = $infoCity['cities'][0]['name'];
        $insee = $infoCity['cities'][0]['insee'];


        $user = $entityManager->getRepository(User::class)->find($userId);

        if (empty($user)) {
            $this->addFlash(
                'warning',
                "Impossible de modifier la tâche"
            );
            return $this->redirectToRoute('profile_show');
        }

        $user->setLocality($locality);
        $user->setInsee($insee);
        $user->setCp($getCp);

        $entityManager->flush();

        $this->addFlash(
            'success',
            "La tâche « $getCp » a été modifiée avec succès"
        );

        return $this->redirectToRoute('profile_show');
    }

}