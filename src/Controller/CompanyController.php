<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Historic;
use App\Entity\User;
use App\Form\CompanyType;
use App\Form\ContactType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/company')]
class CompanyController extends AbstractController
{

    #[Route('/new', name: 'company_new', methods: ['POST'])]
    public function new(Request $request, ValidatorInterface $validator, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        $validate = $validator->validate($company, null, 'Company');
        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
        }
        $em->persist($company);
        $em->flush();
        return new JsonResponse('Company created', Response::HTTP_OK);
    }

    /**
     * @Route("/get", name="company", methods={"POST"})
     * @param CompanyRepository $companyRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     *
     * Gére le get avec un ou plusieur champs (tout les champs possible dans l'entité) et le getall si rien n'est renseigné
     */
    public function find(CompanyRepository $companyRepository, SerializerInterface $serializer, Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(Company::class)->getFieldNames();
        $content = (array)json_decode($request->getContent());
        foreach ($metadata as $value) {
            if (isset($content[$value])) {
                $filter[$value] = $content[$value];
            }
        }
        return JsonResponse::fromJsonString($this->serializeJson($companyRepository->findBy($filter)), Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'company_edit', methods: ['PUT'])]
    public function edit(Request $request, Company $company = null, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        if (!$company) {
            return new JsonResponse("Contact not found", Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(ContactType::class, $company);
        return $this->validAndInsert($request, $form, $validator, $company, $em) ?
            new JsonResponse("Contact updated", Response::HTTP_OK) :
            new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'company_delete', methods: ['DELETE'])]
    public function delete(Request $request, Company $company): Response
    {
        if ($this->isCsrfTokenValid('delete' . $company->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($company);
            $entityManager->flush();
        }

        return new JsonResponse('Company deleted', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param ValidatorInterface $validator
     * @param Company $company
     * @param EntityManagerInterface $em
     * @return boolean
     */
    public function validAndInsert(Request $request, FormInterface $form, ValidatorInterface $validator, Company $company, EntityManagerInterface $em): bool
    {
        $json_decode = json_decode($request->getContent(), true);
        $form->submit($json_decode);
        $validate = $validator->validate($company, null, 'Register');
        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return false;
            }
        }
        $em->persist($company);
        $em->flush();

        return true;
    }

    private function serializeJson($objet){
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getName();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objet, 'json');
    }

    public function createHistoric(Company $company, User $user, $type, $contact) {
        $historic = new Historic();
        $em = $this->getDoctrine()->getManager();
        $historic->setCompany($company);
        $historic->setUsers($user);
        $historic->setType($type);
        $historic->setContact($contact);
        $em->persist($historic);
        $em->flush();
    }

}
