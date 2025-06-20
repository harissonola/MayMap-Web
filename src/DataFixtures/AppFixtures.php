<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\TypeEstablishment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Service\TranslationService;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

class AppFixtures extends Fixture
{
    private TranslationService $translationService;
    private array $supportedLanguages = ['fr', 'en', 'es'];
    private string $defaultLocale = 'fr';

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function load(ObjectManager $manager): void
    {
        $categoriesData = [
            'Santé & Bien êtres' => [
                'Hôpital',
                'Clinique',
                'Pharmacie',
                'Cabinet médical',
                'Centre de santé',
                'Laboratoire d\'analyses',
                'Centre de radiologie',
                'Maternité',
                'Dispensaire'
            ],
            'Restaurants & Alimentations' => [
                'Restaurant gastronomique',
                'Fast-food',
                'Pizzeria',
                'Restaurant traditionnel',
                'Brasserie',
                'Cafétéria',
                'Food truck',
                'Restaurant buffet',
                'Snack-bar',
                'Supermarché',
                'Épicerie',
                'Boulangerie',
                'Pâtisserie',
                'Boucherie',
                'Poissonnerie'
            ],
            'Tourismes & Hébergements' => [
                'Hôtel 5 étoiles',
                'Hôtel 4 étoiles',
                'Hôtel 3 étoiles',
                'Hôtel boutique',
                'Auberge',
                'Motel',
                'Guesthouse',
                'Résidence hôtelière',
                'Camping',
                'Maison d\'hôtes'
            ],
            'Boutiques & Commerces' => [
                'Centre commercial',
                'Supermarché',
                'Boutique de vêtements',
                'Magasin d\'électronique',
                'Librairie',
                'Pharmacie',
                'Bijouterie',
                'Magasin de chaussures',
                'Parfumerie',
                'Magasin de sport'
            ],
            'Événements & Divertissements' => [
                'Salle de concert',
                'Théâtre',
                'Cinéma',
                'Discothèque',
                'Bar',
                'Pub',
                'Karaoke',
                'Bowling',
                'Salle de jeux',
                'Casino'
            ],
            'Sports & Loisirs' => [
                'Salle de sport',
                'Piscine',
                'Tennis',
                'Football',
                'Basketball',
                'Stade',
                'Centre de fitness',
                'Yoga',
                'Danse',
                'Arts martiaux'
            ],
            'Modes & Beautés' => [
                'Salon de coiffure',
                'Institut de beauté',
                'Spa',
                'Barbier',
                'Manucure',
                'Massage',
                'Boutique de mode',
                'Styliste',
                'Parfumerie',
                'Cosmétique'
            ],
            'Médias & Cultures' => [
                'Bibliothèque',
                'Musée',
                'Galerie d\'art',
                'Centre culturel',
                'Maison de la culture',
                'Studio d\'enregistrement',
                'Radio',
                'Télévision',
                'Journal',
                'Édition'
            ],
            'Informatiques & Technologies' => [
                'Magasin informatique',
                'Réparation téléphone',
                'Cybercafé',
                'Centre de formation IT',
                'Développement web',
                'Maintenance informatique',
                'Vente d\'ordinateurs',
                'Accessoires tech',
                'Impression 3D',
                'Robotique'
            ],
            'Éducations & Formations' => [
                'École primaire',
                'Collège',
                'Lycée',
                'Université',
                'École privée',
                'Centre de formation',
                'École de langue',
                'Auto-école',
                'École d\'art',
                'École de musique'
            ],
            'Banques & Finances' => [
                'Banque',
                'Bureau de change',
                'Assurance',
                'Crédit',
                'Microfinance',
                'Coopérative',
                'ATM/DAB',
                'Western Union',
                'Money Gram',
                'Mobile Money'
            ],
            'Mobilités & Transports' => [
                'Gare routière',
                'Arrêt de bus',
                'Taxi',
                'Moto-taxi',
                'Agence de voyage',
                'Location de voiture',
                'Garage automobile',
                'Station-service',
                'Parking',
                'Transport en commun'
            ],
            'BTP & Artisanats' => [
                'Menuiserie',
                'Plomberie',
                'Électricité',
                'Maçonnerie',
                'Peinture',
                'Carrelage',
                'Toiture',
                'Soudure',
                'Ferronnerie',
                'Architecture'
            ],
            'Logements & Hébergements' => [
                'Agence immobilière',
                'Location appartement',
                'Vente maison',
                'Résidence',
                'Studio',
                'Villa',
                'Duplex',
                'Chambre meublée',
                'Colocation',
                'Syndic'
            ],
            'Cultes & Religions' => [
                'Église catholique',
                'Église protestante',
                'Mosquée',
                'Temple',
                'Synagogue',
                'Centre spirituel',
                'Paroisse',
                'Communauté religieuse',
                'École coranique',
                'Catéchisme'
            ],
            'Institutions & Services publics' => [
                'Mairie',
                'Préfecture',
                'Commissariat',
                'Gendarmerie',
                'Tribunal',
                'Poste',
                'Centre des impôts',
                'CNSS',
                'État civil',
                'Service public'
            ],
            'Agricultures & Environnements' => [
                'Ferme',
                'Élevage',
                'Plantation',
                'Coopérative agricole',
                'Marché aux bestiaux',
                'Pépinière',
                'Jardin botanique',
                'Parc national',
                'Réserve naturelle',
                'Centre environnemental'
            ],
            'ONG, Associations Humanitaires' => [
                'ONG internationale',
                'Association locale',
                'Fondation',
                'Organisme humanitaire',
                'Centre social',
                'Aide aux démunis',
                'Protection de l\'enfance',
                'Droits humains',
                'Développement communautaire',
                'Coopération internationale'
            ],
            'Industries & Productions' => [
                'Usine textile',
                'Industrie alimentaire',
                'Brasserie',
                'Cimenterie',
                'Raffinerie',
                'Manufacture',
                'Atelier de production',
                'Zone industrielle',
                'Transformation',
                'Conditionnement'
            ]
        ];

        /** @var TranslationRepository $translationRepo */
        $translationRepo = $manager->getRepository(Translation::class);

        foreach ($categoriesData as $categoryName => $types) {
            // Création de la catégorie
            $category = new Category();
            $category->setName($categoryName);
            $category->setSlug($this->createSlug($categoryName));
            $category->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($category);

            // Traduction seulement du nom de la catégorie (pas du slug)
            $translations = $this->translationService->translateToMultipleLanguages(
                $categoryName,
                $this->defaultLocale,
                array_diff($this->supportedLanguages, [$this->defaultLocale])
            );

            foreach ($translations as $locale => $translation) {
                $translationRepo->translate($category, 'name', $locale, $translation);
            }

            // Création des types pour cette catégorie
            foreach ($types as $typeName) {
                $type = new TypeEstablishment();
                $type->setName($typeName);
                $type->setSlug($this->createSlug($typeName));
                $type->setCreatedAt(new \DateTimeImmutable());
                $type->setCategory($category);
                $manager->persist($type);

                // Traduction seulement du nom du type (pas du slug)
                $typeTranslations = $this->translationService->translateToMultipleLanguages(
                    $typeName,
                    $this->defaultLocale,
                    array_diff($this->supportedLanguages, [$this->defaultLocale])
                );

                foreach ($typeTranslations as $locale => $translation) {
                    $translationRepo->translate($type, 'name', $locale, $translation);
                }
            }
        }

        $manager->flush();
    }

    private function createSlug(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
}