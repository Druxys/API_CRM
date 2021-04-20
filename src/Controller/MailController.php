<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{
    #[Route('/mail', name: 'mail')]
    public function index(Request $request,\Swift_Mailer $mailer): Response
    {
        $content = json_decode($request->getContent(),true);

        if ($content) {
            // On crée le message
            $message = (new \Swift_Message('Nouveau contact'))
                // On attribue l'expéditeur
                ->setFrom($content['email'])
                // On attribue le destinataire
                ->setTo('votre@adresse.fr')
                // On crée le texte avec la vue
                ->setBody(
                    $this->renderView(
                        'mail/index.html.twig'
                    ),
                    'text/html'
                )
                ->attach(\Swift_Attachment::fromPath($content['pdf']))
            ;
            $mailer->send($message);
        } else {
            return new JsonResponse('empty', Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('mail send', Response::HTTP_OK);
    }
}
