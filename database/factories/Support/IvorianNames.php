<?php

namespace Database\Factories\Support;

final class IvorianNames
{
    private const FIRST_NAMES = [
        'Aya', 'Adjoua', 'Akissi', 'Amenan', 'Affoué', 'Awa', 'Fatoumata', 'Mariam', 'Aminata', 'Salimata', 'Kadidja', 'Nathalie',
        'Kouamé', 'Yao', 'Koffi', 'Kouadio', 'Konan', 'Mamadou', 'Ibrahim', 'Seydou', 'Souleymane', 'Moussa', 'Lassina', 'Drissa',
        'Yacouba', 'Sékou', 'Jean-Marc', 'Serge', 'Hervé', 'Olivier', 'Arsène', 'Cédric', 'Désiré', 'Fabrice',
    ];

    private const LAST_NAMES = [
        'Kouassi', 'Koffi', 'Konan', 'Yao', 'Kouadio', 'N\'Guessan', 'Aka', 'Brou', 'Koné', 'Traoré', 'Ouattara', 'Coulibaly',
        'Diabaté', 'Bamba', 'Sangaré', 'Fofana', 'Yapi', 'Gnagne', 'Dago', 'Zézé', 'Aké', 'Assamoi', 'Tanoh', 'Djédjé',
        'Soro', 'Silué', 'Yéo', 'Tuo', 'Cissé', 'Diomandé', 'Doumbia', 'Touré', 'Kanté', 'Gbané',
    ];

    public static function first(): string
    {
        return fake()->randomElement(self::FIRST_NAMES);
    }

    public static function last(): string
    {
        return fake()->randomElement(self::LAST_NAMES);
    }

    public static function full(): string
    {
        return self::first().' '.self::last();
    }
}
