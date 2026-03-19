<?php

namespace App\Controller;

use App\Entity\Abonne;
use App\Form\InscriptionType;
use App\Repository\AbonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    // ─── PAGE D'INSCRIPTION ──────────────────────────────────────────────────
    #[Route('/newsletter', name: 'app_newsletter_index')]
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $abonne = new Abonne();
        $form   = $this->createForm(InscriptionType::class, $abonne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Enregistrer l'abonné en BDD
            $abonne->setInscritLe(new \DateTimeImmutable());
            $abonne->setActif(true);
            $em->persist($abonne);
            $em->flush();

            // Envoyer un email de bienvenue avec TemplatedEmail
            // TemplatedEmail = email dont le corps est un template Twig
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@monsite.fr', 'Mon Site'))
                ->to($abonne->getEmail())
                ->subject('Bienvenue dans notre newsletter !')
                // Le template Twig qui sera rendu comme corps de l'email
                ->htmlTemplate('emails/bienvenue.html.twig')
                // Variables passées au template
                ->context([
                    'abonne' => $abonne,
                ]);

            // $mailer->send() envoie l'email via le transport configuré dans .env
            $mailer->send($email);

            $this->addFlash('success', 'Inscription réussie ! Vérifiez votre boîte email.');

            return $this->redirectToRoute('app_newsletter_index');
        }

        return $this->render('newsletter/index.html.twig', [
            'form' => $form,
        ]);
    }

    // ─── LISTE des abonnés (admin) ───────────────────────────────────────────
    #[Route('/newsletter/abonnes', name: 'app_newsletter_abonnes')]
    public function abonnes(AbonneRepository $repo): Response
    {
        $abonnes = $repo->findBy([], ['inscritLe' => 'DESC']);

        return $this->render('newsletter/abonnes.html.twig', [
            'abonnes' => $abonnes,
        ]);
    }

    // ─── ENVOYER une newsletter à tous les abonnés ───────────────────────────
    #[Route('/newsletter/envoyer', name: 'app_newsletter_envoyer')]
    public function envoyer(
        Request           $request,
        AbonneRepository  $repo,
        MailerInterface   $mailer
    ): Response {

        if ($request->isMethod('POST')) {

            $sujet   = $request->request->get('sujet', '');
            $contenu = $request->request->get('contenu', '');

            // Récupérer tous les abonnés actifs
            $abonnes = $repo->findBy(['actif' => true]);

            // Envoyer un email à chaque abonné
            foreach ($abonnes as $abonne) {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@monsite.fr', 'Mon Site'))
                    ->to($abonne->getEmail())
                    ->subject($sujet)
                    ->htmlTemplate('emails/newsletter.html.twig')
                    ->context([
                        'abonne'  => $abonne,
                        'sujet'   => $sujet,
                        'contenu' => $contenu,
                    ]);

                $mailer->send($email);
            }

            $this->addFlash('success', count($abonnes) . ' email(s) envoyé(s) avec succès !');

            return $this->redirectToRoute('app_newsletter_envoyer');
        }

        return $this->render('newsletter/envoyer.html.twig');
    }

    // ─── SE DÉSABONNER ───────────────────────────────────────────────────────
    #[Route('/newsletter/desabonner/{email}', name: 'app_newsletter_desabonner')]
    public function desabonner(string $email, AbonneRepository $repo, EntityManagerInterface $em): Response
    {
        $abonne = $repo->findOneBy(['email' => $email]);

        if ($abonne) {
            $em->remove($abonne);
            $em->flush();
        }

        return $this->render('newsletter/desabonne.html.twig');
    }
}