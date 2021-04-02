<?php

namespace App\Controller;

use App\Entity\AgendaLine;
use App\Form\AgendaLineType;
use App\Repository\AgendaLineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agenda/line')]
class AgendaLineController extends AbstractController
{
    #[Route('/', name: 'agenda_line_index', methods: ['GET'])]
    public function index(AgendaLineRepository $agendaLineRepository): Response
    {
        return $this->render('agenda_line/index.html.twig', [
            'agenda_lines' => $agendaLineRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'agenda_line_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $agendaLine = new AgendaLine();
        $form = $this->createForm(AgendaLineType::class, $agendaLine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($agendaLine);
            $entityManager->flush();

            return $this->redirectToRoute('agenda_line_index');
        }

        return $this->render('agenda_line/new.html.twig', [
            'agenda_line' => $agendaLine,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agenda_line_show', methods: ['GET'])]
    public function show(AgendaLine $agendaLine): Response
    {
        return $this->render('agenda_line/show.html.twig', [
            'agenda_line' => $agendaLine,
        ]);
    }

    #[Route('/{id}/edit', name: 'agenda_line_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AgendaLine $agendaLine): Response
    {
        $form = $this->createForm(AgendaLineType::class, $agendaLine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('agenda_line_index');
        }

        return $this->render('agenda_line/edit.html.twig', [
            'agenda_line' => $agendaLine,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agenda_line_delete', methods: ['POST'])]
    public function delete(Request $request, AgendaLine $agendaLine): Response
    {
        if ($this->isCsrfTokenValid('delete'.$agendaLine->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($agendaLine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agenda_line_index');
    }
}
