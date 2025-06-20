<?php
// src/EventListener/AutoTranslationListener.php

namespace App\EventListener;

use App\Entity\User;
use App\Service\TranslationService;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class AutoTranslationListener
{
    private array $supportedLanguages = ['fr', 'en', 'es'];
    private string $defaultLocale = 'fr';

    public function __construct(
        private readonly Security $security,
        private readonly TranslationService $translationService,
        private readonly TranslatableListener $translatableListener
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->handleTranslations($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->handleTranslations($args->getObject());
    }

    private function handleTranslations(object $entity): void
    {
        if (!method_exists($entity, 'getTranslations')) {
            return;
        }

        $user = $this->security->getUser();
        $userLanguage = $user instanceof User
            ? ($user->getLanguage() ?? $this->defaultLocale)
            : $this->defaultLocale;

        $targetLanguages = array_diff($this->supportedLanguages, [$userLanguage]);

        $reflection = new \ReflectionClass($entity);
        $translatableProperties = array_filter(
            $reflection->getProperties(),
            fn($prop) => !empty($prop->getAttributes('Gedmo\Mapping\Annotation\Translatable'))
        );

        foreach ($translatableProperties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($entity);

            if (empty($value)) {
                continue;
            }

            $translations = $this->translationService->translateToMultipleLanguages(
                $value,
                $userLanguage,
                $targetLanguages
            );

            foreach ($translations as $locale => $translation) {
                $entity->translate($locale, false)->set($property->getName(), $translation);
            }
        }

        // Ajoutez cette ligne cruciale
        $entity->mergeNewTranslations();
    }
}