<?php
namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, TransportInterface $transport): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from('contact@liranyuzbegi.edenolam.com')
                ->replyTo($data['email'])
                ->to('lir.yy23@gmail.com')
                ->subject('New message from MusicApp')
                ->text("Name: {$data['name']}\nEmail: {$data['email']}\nMessage:\n{$data['message']}");

            // Envoie direct, pas via Messenger
            $transport->send($email);

            $this->addFlash('success', $this->translator->trans('contact_message_sent'));
            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}

