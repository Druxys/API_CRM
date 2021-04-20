<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaLine;
use App\Form\AgendaLineType;
use App\Repository\AgendaLineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/agenda/line')]
class AgendaLineController extends AbstractController
{
    /**
     * @Route("/get", name="Agenda", methods={"GET"})
     * @param AgendaLineRepository $AgendaLineRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     *
     * Gére le get avec un ou plusieur champs (tout les champs possible dans l'entité) et le getall si rien n'est renseigné
     */
    public function find(AgendaLineRepository $AgendaLineRepository, SerializerInterface $serializer,Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(AgendaLineRepository::class)->getFieldNames();
        $content = (array) json_decode($request->getContent());
        foreach($metadata as $value){
            if (isset($content[$value])){
                $filter[$value] = $content[$value];
            }
        }
        return JsonResponse::fromJsonString($serializer->serialize($AgendaLineRepository->findBy($filter),"json"),Response::HTTP_OK);
    }

    #[Route('/{id}/new', name: 'agenda_line_new', methods: ['POST'])]
    public function new(Agenda $agenda = null, Request $request, ValidatorInterface $validator ,EntityManagerInterface $em): Response
    {
        if (!$agenda) {
            return new JsonResponse("Agenda not found", Response::HTTP_BAD_REQUEST);
        }
        $document = new AgendaLine();
        $document->setAgenda($agenda);
        $form = $this->createForm(AgendaLineType::class, $document);
        return $this->validAndInsert($request, $form, $validator, $document, $em) ?
            new JsonResponse("AgendaLine created", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}/edit', name: 'agenda_line_edit', methods: ['PUT'])]
    public function edit(Request $request, AgendaLine $agendaLine,  EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        if (!$agendaLine) {
            return new JsonResponse("AgendaLine not found", Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(AgendaLine::class, $agendaLine);
        return $this->validAndInsert($request, $form, $validator, $agendaLine, $em) ?
            new JsonResponse("AgendaLine updated", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);

    }

    #[Route('/{id}', name: 'agenda_line_delete', methods: ['DELETE'])]
    public function delete(Request $request, AgendaLine $agendaLine): Response
    {
        if (!$agendaLine) {
            return new JsonResponse("AgendaLine not found", Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($agendaLine);
        $entityManager->flush();
        return new JsonResponse('AgendaLine deleted', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param ValidatorInterface $validator
     * @param AgendaLine $agendaLine
     * @param EntityManagerInterface $em
     * @return boolean
     */
    public function validAndInsert(Request $request, FormInterface $form, ValidatorInterface $validator, AgendaLine $agendaLine, EntityManagerInterface $em): bool
    {
        $json_decode = json_decode($request->getContent(), true);
        $form->submit($json_decode);
        $validate = $validator->validate($agendaLine, null, 'Register');
        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return false;
            }
        }
        $em->persist($agendaLine);
        $em->flush();

        return true;
    }
}
