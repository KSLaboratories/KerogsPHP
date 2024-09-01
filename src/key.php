<?php
/*
 * (c) Kerogs kerogs.labs@gmail.com
 *
 * This source file is subject to the Mozilla Public License Version 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kerogs\KerogsPhp;

class Key
{
    private $typeKey;
    private static $keyTypes = [
        1 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!@#$%^&*", // Mot de passe complexe
        2 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!", // Mot de passe
        3 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )", // ID, Dossier
        4 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-", // ID, Dossier, Fichier
        5 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789", // ID, Dossier, Fichier, cryptage
        6 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", // ID, dossier, fichier
        7 => "abcdefghijklmnopqrstuvwxyz", // base
        8 => "0123456789", // suite de nombre
        9 => "abcdefghijklmnopqrstuvwxyz0123456789" // ID, cryptage
    ];

    /**
     * Constructor method for Key class.
     *
     * @param int $typeKey Type of the key, corresponds to predefined types.
     */
    public function __construct(int $typeKey)
    {
        if (!array_key_exists($typeKey, self::$keyTypes)) {
            throw new \InvalidArgumentException('Type de clé invalide (choisir un nombre entre 1 et ' . count(self::$keyTypes) . ')');
        }
        $this->typeKey = $typeKey;
    }

    /**
     * Gets the character set for the given type of key.
     *
     * @return string The character set associated with the type of key.
     */
    public function getCharacterSet(): string
    {
        return self::$keyTypes[$this->typeKey];
    }

    /**
     * Generates a random key with a given length.
     *
     * @param int $length The length of the key to generate.
     * @return string The generated key.
     */
    public function keyGeneration(int $length): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('La longueur de la clé doit être supérieure à zéro.');
        }

        $characterSet = $this->getCharacterSet();
        $charsetLength = strlen($characterSet);

        // Generate the key
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $characterSet[rand(0, $charsetLength - 1)];
        }

        return $key;
    }
}
