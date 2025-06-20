<?php
// src/EventListener/UserLanguageListener.php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Translation\LocaleSwitcher;
use Gedmo\Translatable\TranslatableListener;

final class UserLanguageListener
{
    public function __construct(
        private readonly Security $security,
        private readonly LocaleSwitcher $localeSwitcher,
        private readonly TranslatableListener $translatableListener
    ) {
    }

    #[AsEventListener]
    public function onRequestEvent(RequestEvent $event): void
    {
        $user = $this->security->getUser();

        if ($user && $user instanceof User) {
            $userLanguage = $user->getLanguage() ?? 'fr';
            
            // Définit la locale pour l'interface utilisateur
            $this->localeSwitcher->setLocale($userLanguage);
            
            // Définit la locale pour les entités translatables
            $this->translatableListener->setTranslatableLocale($userLanguage);
        } else {
            // Utilisateur non connecté, utilise la locale par défaut
            $defaultLocale = 'fr';
            $this->localeSwitcher->setLocale($defaultLocale);
            $this->translatableListener->setTranslatableLocale($defaultLocale);
        }
    }
}