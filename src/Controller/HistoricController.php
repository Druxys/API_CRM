<?php

namespace App\Controller;

use App\Entity\Historic;
use App\Form\HistoricType;
use App\Repository\HistoricRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/historic')]
class HistoricController extends AbstractController
{
    #[Route('/', name: 'historic_index', methods: ['GET'])]
    public function index(HistoricRepository $historicRepository): Response
    {
        return $this->render('historic/index.html.twig', [
            'historics' => $historicRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'historic_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $historic = new Historic();
        $form = $this->createForm(HistoricType::class, $historic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($historic);
            $entityManager->flush();

            return $this->redirectToRoute('historic_index');
        }

        return $this->render('historic/new.html.twig', [
            'historic' => $historic,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'historic_show', methods: ['GET'])]
    public function show(Historic $historic): Response
    {
        return $this->render('historic/show.html.twig', [
            'historic' => $historic,
        ]);
    }

    #[Route('/{id}/edit', name: 'historic_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Historic $historic): Response
    {
        $form = $this->createForm(HistoricType::class, $historic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('historic_index');
        }

        return $this->render('historic/edit.html.twig', [
            'historic' => $historic,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'historic_delete', methods: ['POST'])]
    public function delete(Request $request, Historic $historic): Response
    {
        if ($this->isCsrfTokenValid('delete'.$historic->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($historic);
            $entityManager->flush();
        }

        return $this->redirectToRoute('historic_index');
    }
}
