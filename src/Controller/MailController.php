<?php

namespace App\Controller;

use App\Form\MailInvoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    #[Route('/mail', name: 'mail', methods: ['POST'])]
    public function index(Request $request, \Swift_Mailer $mailer): Response
    {
        $editForm = $this->createForm(MailInvoiceType::class);
//        $editForm->bind($request);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // On crée le message
            $message = (new \Swift_Message('Nouveau contact'))
                // On attribue l'expéditeur
                ->setFrom($editForm['email'])
                // On attribue le destinataire
                ->setTo('projet.nfactory@gmail.com')
                // On crée le texte avec la vue
                ->setBody(
                    $this->renderView(
                        'mail/index.html.twig'
                    ),
                    'text/html'
                )
                ->attach(\Swift_Attachment::fromPath($editForm['pdf']));
            $mailer->send($message);
            return new JsonResponse('mail send', Response::HTTP_OK);

        }

    }
}
