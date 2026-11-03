<?php

namespace App\Controller;

use App\Form\ContactType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactController extends AbstractController
{
    private const MINIMUM_FILL_TIME = 3;
    private const SEND_COOLDOWN = 60;

    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        TransportInterface $transport,
        TranslatorInterface $translator,
        LoggerInterface $logger,
    ): Response {
        $session = $request->getSession();

        if (!$request->isMethod('POST')) {
            $session->set('contact_form_rendered_at', time());
        }

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = time();
            $renderedAt = (int) $session->get(
                'contact_form_rendered_at',
                0
            );

            $honeypot = trim(
                (string) $form->get('website')->getData()
            );

            $isProbablyBot = $honeypot !== ''
                || $renderedAt === 0
                || ($now - $renderedAt) < self::MINIMUM_FILL_TIME;

            if ($isProbablyBot) {
                $logger->notice(
                    'Contact form submission blocked as probable spam.',
                    [
                        'ip' => $request->getClientIp(),
                        'honeypot_filled' => $honeypot !== '',
                    ]
                );

                /*
                 * On affiche un message de réussite afin que le robot
                 * ne puisse pas comprendre pourquoi son envoi a été bloqué.
                 */
                $this->addFlash(
                    'success',
                    $translator->trans('contact_message_sent')
                );

                return $this->redirectToRoute('contact');
            }

            $lastSentAt = (int) $session->get(
                'contact_form_last_sent_at',
                0
            );

            if (
                $lastSentAt > 0
                && ($now - $lastSentAt) < self::SEND_COOLDOWN
            ) {
                $this->addFlash(
                    'error',
                    'Please wait one minute before sending another message.'
                );

                return $this->redirectToRoute('contact');
            }

            $data = $form->getData();

            $email = (new Email())
                ->from('contact@liranyuzbegi.com')
                ->replyTo($data['email'])
                ->to('lir.yy23@gmail.com')
                ->subject('New message from liranyuzbegi.com')
                ->text(
                    "Name: {$data['name']}\n"
                    . "Email: {$data['email']}\n"
                    . "Message:\n{$data['message']}"
                );

            try {
                $transport->send($email);
            } catch (TransportExceptionInterface $exception) {
                $logger->error(
                    'Contact form email could not be sent.',
                    [
                        'exception' => $exception,
                    ]
                );

                $this->addFlash(
                    'error',
                    'Your message could not be sent. '
                    . 'Please try again later or email '
                    . 'contact@liranyuzbegi.com directly.'
                );

                return $this->redirectToRoute('contact');
            }

            $session->set('contact_form_last_sent_at', $now);

            $this->addFlash(
                'success',
                $translator->trans('contact_message_sent')
            );

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}