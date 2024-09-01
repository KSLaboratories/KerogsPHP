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
        1 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!@#$%^&*", 
        2 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!", 
        3 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )", 
        4 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-", 
        5 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789", 
        6 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
        7 => "abcdefghijklmnopqrstuvwxyz",
        8 => "0123456789",
        9 => "abcdefghijklmnopqrstuvwxyz0123456789"
    ];

    /**
     * Constructor method for Key class.
     * 
     * keylist :
     *  1 : abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!@#$%^&*
     *  2 : abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!
     *  3 : abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )
     *  4 : abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-
     *  5 : abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789
     *  6 : abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
     *  7 : abcdefghijklmnopqrstuvwxyz
     *  8 : 0123456789
     *  9 : abcdefghijklmnopqrstuvwxyz0123456789
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
     * Generates a random key based on the character set associated with the type of key.
     *
     * @param int $length The length of the key to generate.
     *
     * @return string A randomly generated key of the given length based on the associated character set.
     *
     * @throws \InvalidArgumentException If the length of the key is not positive.
     */
    public function keyGeneration(int $length): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('La longueur de la clé doit être supérieure à zéro.');
        }

        $characterSet = $this->getCharacterSet();
        $charsetLength = strlen($characterSet);

        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $characterSet[rand(0, $charsetLength - 1)];
        }

        return $key;
    }
}
