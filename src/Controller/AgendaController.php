<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\User;
use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/agenda')]
class AgendaController extends AbstractController
{
    /**
     * @Route("/get", name="Agenda", methods={"GET"})
     * @param AgendaRepository $AgendaRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     *
     * Gére le get avec un ou plusieur champs (tout les champs possible dans l'entité) et le getall si rien n'est renseigné
     */
    public function find(AgendaRepository $AgendaRepository, SerializerInterface $serializer,Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(AgendaRepository::class)->getFieldNames();
        $content = (array) json_decode($request->getContent());
        foreach($metadata as $value){
            if (isset($content[$value])){
                $filter[$value] = $content[$value];
            }
        }
        return JsonResponse::fromJsonString($serializer->serialize($AgendaRepository->findBy($filter),"json"),Response::HTTP_OK);
    }

    #[Route('/{id}/new', name: 'agenda_new', methods: ['POST'])]
    public function new(User $user = null, Request $request, ValidatorInterface $validator ,EntityManagerInterface $em): Response
    {
        if (!$user) {
            return new JsonResponse("user not found", Response::HTTP_BAD_REQUEST);
        }
        $document = new Agenda();
        $document->setUsers($user);
        $form = $this->createForm(AgendaType::class, $document);
        return $this->validAndInsert($request, $form, $validator, $document, $em) ?
            new JsonResponse("Agenda created", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }


    #[Route('/{id}/edit', name: 'agenda_edit', methods: ['PUT'])]
    public function edit(Request $request, Agenda $agenda = null,  EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        if (!$agenda) {
            return new JsonResponse("Contact not found", Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(AgendaType::class, $agenda);
        return $this->validAndInsert($request, $form, $validator, $agenda, $em) ?
            new JsonResponse("Agenda updated", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'agenda_delete', methods: ['DELETE'])]
    public function delete(Request $request, Agenda $agenda = null): Response
    {
        if (!$agenda) {
            return new JsonResponse("Agenda not found", Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($agenda);
        $entityManager->flush();
        return new JsonResponse('Agenda deleted', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param ValidatorInterface $validator
     * @param Agenda $agenda
     * @param EntityManagerInterface $em
     * @return boolean
     */
    public function validAndInsert(Request $request, FormInterface $form, ValidatorInterface $validator, Agenda $agenda, EntityManagerInterface $em): bool
    {
        $json_decode = json_decode($request->getContent(), true);
        $form->submit($json_decode);
        $validate = $validator->validate($agenda, null, 'Register');
        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return false;
            }
        }
        $em->persist($agenda);
        $em->flush();

        return true;
    }
}
