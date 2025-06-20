<?php

namespace App\Service;

use App\Entity\Establishment;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class VerificationEmailSender
{
    private MailerInterface $mailer;
    
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    
    public function sendVerificationEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@maymap.com')
            ->to($user->getEmail())
            ->subject('Vérification de votre compte MayMap')
            ->htmlTemplate('emails/verification.html.twig')
            ->context([
                'user' => $user,
                'code' => $user->getVerificationCode(),
                'expiration_date' => $user->getVerificationCodeExpiresAt()->format('d/m/Y H:i')
            ]);
            
        $this->mailer->send($email);
    }
    
    public function notifyAdminsForVerification(Establishment $establishment): void
    {
        $adminEmail = 'admin@maymap.com';
        $email = (new TemplatedEmail())
            ->from('no-reply@maymap.com')
            ->to($adminEmail)
            ->subject('[MayMap] Nouvel établissement à vérifier')
            ->htmlTemplate('emails/admin_verification.html.twig')
            ->context([
                'establishment' => $establishment,
                'owner' => $establishment->getOwner()
            ]);
            
        $this->mailer->send($email);
    }
    
    public function sendApprovalNotification(Establishment $establishment): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@maymap.com')
            ->to($establishment->getOwner()->getEmail())
            ->subject('Votre établissement a été approuvé')
            ->htmlTemplate('emails/establishment_approved.html.twig')
            ->context([
                'establishment' => $establishment
            ]);
            
        $this->mailer->send($email);
    }
}