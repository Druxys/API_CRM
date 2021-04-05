<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Contact;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/document')]
class DocumentController extends AbstractController
{
    /**
     * @Route("/get", name="Document", methods={"GET"})
     * @param DocumentRepository $DocumentRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     *
     * Gére le get avec un ou plusieur champs (tout les champs possible dans l'entité) et le getall si rien n'est renseigné
     */
    public function find(DocumentRepository $DocumentRepository, SerializerInterface $serializer,Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(DocumentRepository::class)->getFieldNames();
        $content = (array) json_decode($request->getContent());
        foreach($metadata as $value){
            if (isset($content[$value])){
                $filter[$value] = $content[$value];
            }
        }
        return JsonResponse::fromJsonString($serializer->serialize($DocumentRepository->findBy($filter),"json"),Response::HTTP_OK);
    }

    #[Route('/{id}/new', name: 'document_new', methods: ['POST'])]
    public function new(Company $company = null, Request $request, ValidatorInterface $validator ,EntityManagerInterface $em): Response
    {
        if (!$company) {
            return new JsonResponse("Company not found", Response::HTTP_BAD_REQUEST);
        }
        $document = new Document();
        $document->setCompany($company);
        $form = $this->createForm(DocumentType::class, $document);
        return $this->validAndInsert($request, $form, $validator, $document, $em) ?
            new JsonResponse("Document created", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}/edit', name: 'document_edit', methods: ['PUT'])]
    public function edit(Request $request, Document $document = null, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        if (!$document) {
            return new JsonResponse("Contact not found", Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(DocumentType::class, $document);
        return $this->validAndInsert($request, $form, $validator, $document, $em) ?
            new JsonResponse("Contact updated", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'document_delete', methods: ['DELETE'])]
    public function delete(Request $request, Document $document = null): Response
    {
        if (!$document) {
            return new JsonResponse("Document not found", Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($document);
        $entityManager->flush();
        return new JsonResponse('Document deleted', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param ValidatorInterface $validator
     * @param Document $document
     * @param EntityManagerInterface $em
     * @return boolean
     */
    public function validAndInsert(Request $request, FormInterface $form, ValidatorInterface $validator, Document $document, EntityManagerInterface $em): bool
    {
        $json_decode = json_decode($request->getContent(), true);
        $form->submit($json_decode);
        $validate = $validator->validate($document, null, 'Register');
        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return false;
            }
        }
        $em->persist($document);
        $em->flush();

        return true;
    }
}
