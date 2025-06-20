<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('iconForCategory', [$this, 'getCategoryIcon']),
            new TwigFunction('iconForType', [$this, 'getTypeIcon']),
        ];
    }

    public function getCategoryIcon(string $categoryName): string
    {
        $icons = [
            // Français (exact matches from DB)
            'Santé & Bien êtres' => 'heartbeat',
            'Restaurants & Alimentations' => 'utensils',
            'Tourismes & Hébergements' => 'hotel',
            'Boutiques & Commerces' => 'shopping-bag',
            'Événements & Divertissements' => 'music',
            'Sports & Loisirs' => 'dumbbell',
            'Modes & Beautés' => 'cut',
            'Médias & Cultures' => 'book',
            'Informatiques & Technologies' => 'laptop',
            'Éducations & Formations' => 'graduation-cap',
            'Banques & Finances' => 'university',
            'Mobilités & Transports' => 'bus',
            'BTP & Artisanats' => 'hammer',
            'Logements & Hébergements' => 'home',
            'Cultes & Religions' => 'pray',
            'Institutions & Services publics' => 'landmark',
            'Agricultures & Environnements' => 'seedling',
            'ONG, Associations Humanitaires' => 'hands-helping',
            'Industries & Productions' => 'industry',
            
            // Espagnol (exact matches from DB)
            'Salud y seres bien' => 'heartbeat',
            'Restaurantes y suministros de energía' => 'utensils',
            'Turismo y alojamiento' => 'hotel',
            'Boutiques y tiendas' => 'shopping-bag',
            'Eventos y entretenimiento' => 'music',
            'Deportes y ocio' => 'dumbbell',
            'Modos y bellezas' => 'cut',
            'Medios y culturas' => 'book',
            'TI y tecnologías' => 'laptop',
            'Educación y capacitación' => 'graduation-cap',
            'Bancos y finanzas' => 'university',
            'Movilidad y transporte' => 'bus',
            'BTP & Crafts' => 'hammer',
            'Vivienda y alojamiento' => 'home',
            'Cultos y religiones' => 'pray',
            'Instituciones y servicios públicos' => 'landmark',
            'Agricultura y medio ambiente' => 'seedling',
            'ONG, asociaciones humanitarias' => 'hands-helping',
            'Industrias y producciones' => 'industry',
            
            // Anglais (exact matches from DB)
            'Health & Bien Beings' => 'heartbeat',
            'Restaurants & power supplies' => 'utensils',
            'Tourism & accommodation' => 'hotel',
            'Boutiques and shops' => 'shopping-bag',
            'Events & entertainment' => 'music',
            'Sports & leisure' => 'dumbbell',
            'Modes & Beauties' => 'cut',
            'Media & Cultures' => 'book',
            'IT & Technologies' => 'laptop',
            'Educations and training' => 'graduation-cap',
            'Banks and finances' => 'university',
            'Mobility and transport' => 'bus',
            'BTP & crafts' => 'hammer',
            'Housing and accommodation' => 'home',
            'Cults and religions' => 'pray',
            'Public institutions & services' => 'landmark',
            'Agriculture & Environment' => 'seedling',
            'NGOs, humanitarian associations' => 'hands-helping',
            'Industries & Productions' => 'industry',
        ];

        return $icons[$categoryName] ?? 'store';
    }

    public function getTypeIcon(string $typeName): string
    {
        $icons = [
            // Health & Bien Beings - Salud y seres bien - Santé & Bien êtres
            'Hospital' => 'hospital',
            'Clinical' => 'clinic-medical',
            'Pharmacy' => 'prescription-bottle-medical',
            'Medical office' => 'user-md',
            'Health center' => 'plus-circle',
            'Analysis laboratory' => 'flask',
            'Radiology center' => 'x-ray',
            'Maternity' => 'baby',
            'Dispensary' => 'medical-kit',

            // Restaurants & power supplies - Restaurantes y suministros de energía - Restaurants & Alimentations
            'Gourmet' => 'utensils',
            'Fast food' => 'hamburger',
            'Pizzeria' => 'pizza-slice',
            'Traditional restaurant' => 'utensils',
            'Brewery' => 'beer',
            'Cafeteria' => 'coffee',
            'Food truck' => 'truck',
            'Buffet restaurant' => 'utensils',
            'Snack bar' => 'candy-bar',
            'Supermarket' => 'shopping-cart',
            'Grocery store' => 'store',
            'Bakery' => 'bread-slice',
            'Pastry shop' => 'birthday-cake',
            'Butcher\'s shop' => 'drumstick-bite',
            'Fishmonger' => 'fish',

            // Tourism & accommodation - Turismo y alojamiento - Tourismes & Hébergements
            '5 -star hotel' => 'hotel',
            '4 -star hotel' => 'hotel',
            '3 -star hotel' => 'hotel',
            'Shop shop' => 'building', // Note: This appears to be a typo in the DB ('Hôtel boutique' in French)
            'Hostel' => 'house',
            'Motel' => 'bed',
            'Guesthouse' => 'home',
            'Hotel residence' => 'building',
            'Camping' => 'campground',
            'Guest House' => 'home',

            // Boutiques and shops - Boutiques y tiendas - Boutiques & Commerces
            'Mall' => 'shopping-basket',
            'Clothing shop' => 'tshirt',
            'Electronic store' => 'tv',
            'Library' => 'book',
            'Jewelry' => 'gem',
            'Shoe store' => 'shoe-prints',
            'Perfumery' => 'spray-can',
            'Sports store' => 'running',

            // Events & entertainment - Eventos y entretenimiento - Événements & Divertissements
            'Concert hall' => 'music',
            'Theater' => 'masks-theater',
            'Movie theater' => 'film',
            'Disco' => 'compact-disc',
            'Bar' => 'glass-martini',
            'Pub' => 'beer',
            'Karaoke' => 'microphone',
            'Bowling' => 'bowling-ball',
            'Playroom' => 'gamepad',
            'Casino' => 'dice',

            // Sports & leisure - Deportes y ocio - Sports & Loisirs
            'Gym' => 'dumbbell',
            'Pool' => 'swimmer',
            'Tennis' => 'table-tennis',
            'Soccer' => 'football-ball',
            'Basketball' => 'basketball-ball',
            'Stadium' => 'futbol',
            'Fitness center' => 'dumbbell',
            'Yoga' => 'om',
            'Dance' => 'walking',
            'Martial arts' => 'fist-raised',

            // Modes & Beauties - Modos y bellezas - Modes & Beautés
            'Hair salon' => 'cut',
            'Beauty Institute' => 'spa',
            'Spa' => 'spa',
            'Barber' => 'cut',
            'Manicure' => 'hand-sparkles',
            'Massage' => 'hands',
            'Fashion shop' => 'tshirt',
            'Stylist' => 'user-tie',
            'Cosmetic' => 'palette',

            // Media & Cultures - Medios y culturas - Médias & Cultures
            'Library' => 'book-open',
            'Museum' => 'landmark',
            'Art gallery' => 'palette',
            'Cultural center' => 'theater-masks',
            'Culture House' => 'building',
            'Recording studio' => 'microphone',
            'Radio' => 'broadcast-tower',
            'Television' => 'tv',
            'Newspaper' => 'newspaper',
            'Edition' => 'book',

            // IT & Technologies - TI y tecnologías - Informatiques & Technologies
            'Computer store' => 'laptop',
            'Telephone repair' => 'mobile-alt',
            'Cybercafé' => 'wifi',
            'IT training center' => 'code',
            'Web development' => 'code',
            'IT maintenance' => 'screwdriver',
            'Sale of computers' => 'desktop',
            'Tech accessories' => 'plug',
            '3D printing' => 'cube',
            'Robotics' => 'robot',

            // Educations and training - Educación y capacitación - Éducations & Formations
            'Primary school' => 'school',
            'College' => 'school',
            'High school' => 'school-flag',
            'University' => 'university',
            'Private school' => 'graduation-cap',
            'Training center' => 'chalkboard-teacher',
            'Language school' => 'language',
            'Driving school' => 'car',
            'Art school' => 'paint-brush',
            'Music school' => 'music',

            // Banks and finances - Bancos y finanzas - Banques & Finances
            'Bank' => 'university',
            'Exchange office' => 'exchange-alt',
            'Insurance' => 'shield-alt',
            'Credit' => 'credit-card',
            'Microfinance' => 'hand-holding-usd',
            'Cooperative' => 'handshake',
            'ATM/DAB' => 'credit-card',
            'Western Union' => 'money-bill-wave',
            'Money Gram' => 'money-bill',
            'Mobile Money' => 'mobile-alt',

            // Mobility and transport - Movilidad y transporte - Mobilités & Transports
            'Bus station' => 'bus-station',
            'Bus stop' => 'bus',
            'Taxi' => 'taxi',
            'Motorcycle taxi' => 'motorcycle',
            'Travel agency' => 'plane',
            'Car rental' => 'car-alt',
            'Car garage' => 'car-crash',
            'Service station' => 'gas-pump',
            'Parking' => 'parking',
            'Public transport' => 'subway',

            // BTP & crafts - BTP & Crafts - BTP & Artisanats
            'Carpentry' => 'hammer',
            'Plumbing' => 'wrench',
            'Electricity' => 'bolt',
            'Masonry' => 'hard-hat',
            'Paint' => 'paint-roller',
            'Tiling' => 'th-large',
            'Roof' => 'home',
            'Welding' => 'fire',
            'Ironwork' => 'hammer',
            'Architecture' => 'drafting-compass',

            // Housing and accommodation - Vivienda y alojamiento - Logements & Hébergements
            'Real estate agency' => 'key',
            'Rental Apartment' => 'door-open',
            'House sale' => 'home',
            'Residence' => 'building',
            'Studio' => 'bed',
            'Villa' => 'house-user',
            'Duplex' => 'layer-group',
            'Furnished room' => 'bed',
            'Shared' => 'users',
            'Trustee' => 'clipboard-list',

            // Cults and religions - Cultos y religiones - Cultes & Religions
            'Catholic Church' => 'cross',
            'Protestant church' => 'cross',
            'Mosque' => 'mosque',
            'Temple' => 'place-of-worship',
            'Synagogue' => 'star-of-david',
            'Spiritual center' => 'pray',
            'Parish' => 'church',
            'Religious community' => 'praying-hands',
            'Koranic school' => 'quran',
            'Catechism' => 'bible',

            // Public institutions & services - Instituciones y servicios públicos - Institutions & Services publics
            'City hall' => 'city',
            'Prefecture' => 'landmark',
            'Police station' => 'shield-alt',
            'Gendarmerie' => 'user-shield',
            'Court' => 'balance-scale',
            'Job' => 'mail-bulk',
            'Tax center' => 'file-invoice-dollar',
            'CNSS' => 'id-card',
            'Marital status' => 'certificate',
            'Public service' => 'landmark',

            // Agriculture & Environment - Agricultura y medio ambiente - Agricultures & Environnements
            'Farm' => 'tractor',
            'Breeding' => 'horse',
            'Planting' => 'seedling',
            'Agricultural cooperative' => 'handshake',
            'Cattle market' => 'cow',
            'Nursery' => 'leaf',
            'Botanical garden' => 'tree',
            'National park' => 'mountain',
            'Nature reserve' => 'dove',
            'Environmental center' => 'globe',

            // NGOs, humanitarian associations - ONG, asociaciones humanitarias - ONG, Associations Humanitaires
            'International NGO' => 'globe',
            'Local association' => 'hands-helping',
            'Foundation' => 'hand-holding-heart',
            'Humanitarian' => 'heart',
            'Social center' => 'users',
            'Help with the Depue' => 'hand-holding',
            'Child protection' => 'child',
            'Human rights' => 'fist-raised',
            'Community development' => 'people-carry',
            'International cooperation' => 'handshake',

            // Industries & Productions - Industrias y producciones - Industries & Productions
            'Textile factory' => 'industry',
            'Food industry' => 'cogs',
            'Brewery' => 'beer',
            'Cement plant' => 'hammer',
            'Refinery' => 'oil-can',
            'Factory' => 'industry',
            'Production workshop' => 'cogs',
            'Industrial zone' => 'factory',
            'Transformation' => 'recycle',
            'Packaging' => 'box'
        ];

        return $icons[$typeName] ?? 'store';
    }
}