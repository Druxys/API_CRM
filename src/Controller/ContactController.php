<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/contact')]
class ContactController extends AbstractController
{
    /**
     * @Route("/get", name="User", methods={"GET"})
     * @param ContactRepository $contactRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     *
     * Gére le get avec un ou plusieur champs (tout les champs possible dans l'entité) et le getall si rien n'est renseigné
     */
    public function find(ContactRepository $contactRepository, SerializerInterface $serializer,Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(ContactRepository::class)->getFieldNames();
        $content = (array) json_decode($request->getContent());
        foreach($metadata as $value){
            if (isset($content[$value])){
                $filter[$value] = $content[$value];
            }
        }
        return JsonResponse::fromJsonString($serializer->serialize($contactRepository->findBy($filter),"json"),Response::HTTP_OK);
    }

    #[Route('/{id}/new', name: 'contact_new', methods: ['POST'])]
    public function new(Company $company = null, Request $request,ValidatorInterface $validator ,EntityManagerInterface $em): Response
    {
        if (!$company) {
            return new JsonResponse("Company not found", Response::HTTP_BAD_REQUEST);
        }
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        return $this->validAndInsert($request, $form, $validator, $contact, $em) ?
            new JsonResponse("Contact created", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}/edit', name: 'contact_edit', methods: ['POST'])]
    public function edit(Request $request, Contact $contact = null, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        if (!$contact) {
            return new JsonResponse("Contact not found", Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(ContactType::class, $contact);
        return $this->validAndInsert($request, $form, $validator, $contact, $em) ?
            new JsonResponse("Contact updated", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact = null): Response
    {
        if (!$contact) {
            return new JsonResponse("Contact not found", Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($contact);
        $entityManager->flush();
        return new JsonResponse('Contact deleted', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param ValidatorInterface $validator
     * @param Contact $contact
     * @param EntityManagerInterface $em
     * @return boolean
     */
    public function validAndInsert(Request $request, FormInterface $form, ValidatorInterface $validator, Contact $contact, EntityManagerInterface $em): bool
    {
        $json_decode = json_decode($request->getContent(), true);
        $form->submit($json_decode);
        $validate = $validator->validate($contact, null, 'Register');
        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return false;
            }
        }
        $em->persist($contact);
        $em->flush();

        return true;
    }
}
