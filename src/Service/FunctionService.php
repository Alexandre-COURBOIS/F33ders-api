<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\JsonResponse;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

class FunctionService
{

    /**
     * @return bool
     */
    public function testArrayKey(): bool
    {
        $tab = [];
//        Fonction php qui permet de récupérer tous les arguments passées dans une fonction;
        $args = func_get_args();

        foreach ($args as $arg) {
            // Récupère le tableau qu'on doit comparer dans le array_key_exist et le stock dans la valeur $tableToVerify (ici l' utilisateur);
            if (is_array($arg)) {
                $tableToVerify = [$arg];
                $tableToVerify = $tableToVerify[0];
            } else {
                // Push les arguments à comparer lors de la fonction array_key_exist dans le tableau $tab;
                array_push($tab, $arg);
            }
        }

        // Je boucle sur mon tableau d'argement à comparer
        for ($i = 0; $i < count($tab); $i++) {
            //Je compare mes arguments du tableau $tab index par index avec le tableau à comparer (ici l'utilisateur);
            if (array_key_exists($tab[$i], $tableToVerify)) {
                $test = [];
                array_push($test, $tab[$i]);
            } else {
                // Si l'argument récupérer n'existe pas dans le tableau je stop tout et retourne false;
                return false;
            }
        }
        // Si tout s'execute correctement je renvoi true et tout fonctionne;
        return true;
    }

    public function randomPassword(): string
    {
        $randNumber = rand(0,999);

        $majCaracter = "ABCDEFGHIJKLMOPQRSTUVWXYZ";
        $shuffleMajCaracter = str_shuffle($majCaracter);
        $randNumberToPickCaracter = rand(3,4);
        $returnedChar = substr($shuffleMajCaracter, $randNumberToPickCaracter,$randNumberToPickCaracter);

        $caracter = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $shuffleCaracter = str_shuffle($caracter);
        $randNumberPick = rand(6,7);
        $returnedCaracter = substr($shuffleCaracter, $randNumberPick, $randNumberPick);

        $specialCaracter = "@$!%*?&";
        $shuffleSpecialCaracter = str_shuffle($specialCaracter);
        $randToPick = rand(1,2);
        $returnedSpecial = substr($shuffleSpecialCaracter, $randToPick, $randToPick);

        $result = str_shuffle($randNumber.$returnedChar.$returnedCaracter.$returnedSpecial);

        return $result;
    }
}