<?php
// src/Service/TranslationService.php

namespace App\Service;

use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    private GoogleTranslate $translator;

    public function __construct()
    {
        $this->translator = new GoogleTranslate();
    }

    public function translateToMultipleLanguages(string $text, string $sourceLang, array $targetLangs): array
    {
        $translations = [];
        
        foreach ($targetLangs as $targetLang) {
            if ($targetLang === $sourceLang) {
                $translations[$targetLang] = $text;
                continue;
            }
            
            try {
                $this->translator->setSource($sourceLang);
                $this->translator->setTarget($targetLang); // Correction ici (translator au lieu de translator)
                $translations[$targetLang] = $this->translator->translate($text);
            } catch (\Exception $e) {
                $translations[$targetLang] = $text;
            }
        }
        
        return $translations;
    }
}