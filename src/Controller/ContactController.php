<?php
namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(
        Request $request,
        TransportInterface $transport,
        TranslatorInterface $translator,
        LoggerInterface $logger,
    ): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from('contact@liranyuzbegi.com')
                ->replyTo($data['email'])
                ->to('lir.yy23@gmail.com')
                ->subject('New message from liranyuzbegi.com')
                ->text("Name: {$data['name']}\nEmail: {$data['email']}\nMessage:\n{$data['message']}");

            try {
                $transport->send($email);
            } catch (TransportExceptionInterface $exception) {
                $logger->error('Contact form email could not be sent.', [
                    'exception' => $exception,
                ]);
                $this->addFlash('error', 'Your message could not be sent. Please try again later or email contact@liranyuzbegi.com directly.');

                return $this->redirectToRoute('contact');
            }

            $this->addFlash('success', $translator->trans('contact_message_sent'));
            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
