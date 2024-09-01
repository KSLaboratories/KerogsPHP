<?php
/*
 * (c) Kerogs kerogs.labs@gmail.com
 *
 * This source file is subject to the Mozilla Public License Version 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kerogs\KerogsPhp;

use Kerogs\KerogsPhp\Kpf;

class Encryption
{

    private $encryptionKey;

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function cryptText(string $text, bool $crypType): string
    {
        if ($crypType) {
            return openssl_encrypt($text, 'aes-256-cbc', $this->encryptionKey, 0, $this->encryptionKey);
        } else {
            return openssl_decrypt($text, 'aes-256-cbc', $this->encryptionKey, 0, $this->encryptionKey);
        }
    }

    /**
     * Encrypt or decrypt a file.
     *
     * @param string $filePath Path to the file.
     * @param bool $crypType Whether to encrypt (true) or decrypt (false) the file.
     *
     * @return bool Returns true if the file was successfully encrypted/decrypted, false otherwise.
     *
     * @throws \Exception If the file does not exist, is not a .kpf file, or if the encryption/decryption failed.
     */
    public function cryptFile(string $filePath, bool $crypType): bool
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Le fichier n'existe pas.");
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new \Exception("Impossible de lire le fichier.");
        }

        $kpf = new Kpf();
        $newHeader = "#!!\n    @kpfenc true#~>AES-256-cbc\n~!!#\n";

        if ($crypType) {
            $kpfCryptageBool = $kpf->checkHeader($fileContent);
            if (!$kpfCryptageBool) {
                $encryptedContent = openssl_encrypt($fileContent, 'aes-256-cbc', $this->encryptionKey, 0, $this->encryptionKey);
                if (!$encryptedContent) {
                    throw new \Exception("Impossible de crypter le contenu du fichier.");
                } else {
                    file_put_contents($filePath, $newHeader . $encryptedContent);
                }
            }
        } else {
            if (strpos($fileContent, $newHeader) !== 0) {
                throw new \Exception("Le fichier n'est pas crypté ou l'en-tête est incorrect.");
            }

            $encryptedContent = substr($fileContent, strlen($newHeader));

            $decryptedContent = openssl_decrypt($encryptedContent, 'aes-256-cbc', $this->encryptionKey, 0, $this->encryptionKey);
            if (!$decryptedContent) {
                throw new \Exception("Impossible de décrypter le contenu du fichier.");
            } else {
                file_put_contents($filePath, $decryptedContent);
            }
        }

        return true;
    }
}
