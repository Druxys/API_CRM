<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaLine;
use App\Entity\Historic;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppointmentController extends AbstractController
{
    #[Route('/appointment/create', name: 'appointment_create', methods: ['POST'])]
    public function createRendezVous(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);
        $agenda = new Agenda();
        $agendaLine = new AgendaLine();
        $historic = new Historic();

        $agenda->setUsers($content['emailUser']);
        $agenda->setName($content['name']);
        $agendaLine->setAgenda($agenda);
        $agendaLine->setNotes($content['note']);
        $agendaLine->setDate($content['date']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        return new JsonResponse('Appointement created', Response::HTTP_OK);
    }


}
