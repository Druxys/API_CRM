<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * @Route("/get", name="User", methods={"GET"})
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     *
     * Gére le get avec un ou plusieur champs (tout les champs possible dans l'entité) et le getall si rien n'est renseigné
     */
    public function find(UserRepository $userRepository, SerializerInterface $serializer,Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(User::class)->getFieldNames();
        $content = (array) json_decode($request->getContent());
        foreach($metadata as $value){
            if (isset($content[$value])){
                $filter[$value] = $content[$value];
            }
        }
        return JsonResponse::fromJsonString($serializer->serialize($userRepository->findBy($filter),"json"),Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['PUT'])]
    public function edit(Request $request, User $user = null, EntityManagerInterface $em, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder): Response
    {
        if (!$user) {
            return new JsonResponse("User not found", Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(UserType::class, $user);
        return $this->validAndInsert($request, $form, $validator, $user, $em, $encoder) ?
            new JsonResponse("User updated", Response::HTTP_OK) :
            new JsonResponse("bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/admin/new', name: 'user_new', methods: ['POST'])]
    public function new(Request $request ,ValidatorInterface $validator ,EntityManagerInterface $em ,UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        return $this->validAndInsert($request, $form, $validator, $user, $em, $encoder) ?
            new JsonResponse("User created", Response::HTTP_OK) :
            new JsonResponse("bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/admin/{id}/editRole', name: 'user_edit_role', methods: ['PUT'])]
    public function editRole(Request $request, User $user = null, EntityManagerInterface $em): Response
    {
        if (!$user) {
            return new JsonResponse("User not found", Response::HTTP_BAD_REQUEST);
        }
        $content = json_decode($request->getContent(),true);
        if ($content["action"] == "upgrade"){
            $user->setRoles(["ROLE_ADMIN"]);
        }else if ($content["action"] == "downgrade"){
            $user->setRoles(["ROLE_USER"]);
        }else{
            return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
        }
        $em->persist($user);
        $em->flush();
        return new JsonResponse('Roles user updated',Response::HTTP_OK);
    }

    #[Route('/admin/{id}/delete', name: 'user_delete', methods: ['DELETE'])]
    public function delete(Request $request, User $user = null): Response
    {
        if (!$user) {
            return new JsonResponse("User not found", Response::HTTP_BAD_REQUEST);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse('User deleted', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param ValidatorInterface $validator
     * @param User $user
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @return boolean
     */
    public function validAndInsert(Request $request, FormInterface $form, ValidatorInterface $validator, User $user, EntityManagerInterface $em, $encoder): bool
    {
        $json_decode = json_decode($request->getContent(), true);
        $form->submit($json_decode);
        $validate = $validator->validate($user, null, 'Register');
        if (count($validate) !== 0) {
            foreach ($validate as $error) {
                return false;
            }
        }
        $password = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        $em->persist($user);
        $em->flush();

        return true;
    }
}
