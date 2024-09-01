<?php
/*
 * (c) Kerogs kerogs.labs@gmail.com
 *
 * This source file is subject to the Mozilla Public License Version 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kerogs\KerogsPhp;

class Kpf
{
    public function __construct() {}

    /**
     * Extract the header of a .kpf file and returns the associated information as a string.
     *
     * @param string $path The path to the .kpf file to extract the header from.
     *
     * @return string The extracted header in plaintext.
     *
     * @throws \Exception If the file does not exist, is not a .kpf file, or if the header is not found.
     */
    public function extractHeader($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Le fichier n'existe pas.");
        }

        $file = fopen($path, 'r');
        if (!$file) {
            throw new \Exception("Impossible d'ouvrir le fichier.");
        }

        $firstLine = fgets($file);

        if (strpos($firstLine, '#!!') !== 0) {
            fclose($file);
            throw new \Exception("Le fichier n'est pas un fichier .kpf (Kerogs Programing File).");
        }

        rewind($file);

        $fileContent = fread($file, filesize($path));
        fclose($file);

        $startPos = strpos($fileContent, '#!!');
        $endPos = strpos($fileContent, '~!!#');

        if ($startPos === false || $endPos === false) {
            throw new \Exception("L'en-tête n'a pas été trouvé dans le fichier.");
        }

        $header = substr($fileContent, $startPos, ($endPos + 4) - $startPos);

        return $header;
    }

    /**
     * Extract the content of a .kpf file.
     *
     * @param string $path Path to the .kpf file.
     *
     * @return string The content of the file.
     *
     * @throws \Exception If the file does not exist or is not a .kpf file.
     */
    public function extractContent($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Le fichier n'existe pas.");
        }

        $fileContent = file_get_contents($path);
        if ($fileContent === false) {
            throw new \Exception("Impossible de lire le fichier.");
        }

        if (strpos($fileContent, "#!!") !== 0) {
            throw new \Exception("Le fichier n'est pas un fichier .kpf (Kerogs Programing File).");
        }

        $startPos = strpos($fileContent, '#!!');
        $endPos = strpos($fileContent, '~!!#');

        if ($startPos === false || $endPos === false || $endPos < $startPos) {
            throw new \Exception("L'en-tête n'a pas été trouvé dans le fichier.");
        }

        $contentStart = $endPos + 4;

        $content = substr($fileContent, $contentStart);

        $content = ltrim($content);

        return $content;
    }

    /**
     * Parse the header of a .kpf file and returns the associated information as an array.
     *
     * @param string $header The header to parse.
     *
     * @return string The associated information in JSON format.
     *
     * @throws \Exception If the header is not valid.
     */
    public function parseHeader($header)
    {
        $lines = array_filter(array_map('trim', explode("\n", $header)));

        if (empty($lines) || strpos($lines[0], '#!!') !== 0) {
            throw new \Exception("L'en-tête n'est pas valide.");
        }

        $result = [];
        $insideHeader = false;

        foreach ($lines as $line) {
            if ($line === '#!!') {
                $insideHeader = true;
                continue;
            }
            if ($line === '~!!#') {
                $insideHeader = false;
                continue;
            }
            if ($insideHeader && strpos($line, '@') === 0) {
                $line = substr($line, 1);
                $parts = explode(' ', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);

                    if (strpos($value, '#~>') !== false) {
                        $subParts = explode('#~>', $value, 2);
                        $result[$key] = [
                            'enable' => $this->parseValue(trim($subParts[0])),
                            'type' => trim($subParts[1])
                        ];
                    } else {
                        $result[$key] = $this->parseValue($value);
                    }
                }
            }
        }

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    /**
     * Parse a value from a string to a boolean, float, int or string.
     *
     * @param string $value The value to parse.
     *
     * @return bool|float|int|string The parsed value.
     */
    private function parseValue($value) {
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }
        if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                return (float)$value;
            } else {
                return (int)$value;
            }
        }
        return $value;
    }

    /**
     * Search a value in a KPF header.
     *
     * @param string $header The KPF header.
     * @param string $searchKey The key to search, can be a sub key (e.g., "key#~>subkey").
     *
     * @return mixed The searched value, or null if not found.
     *
     * @throws \Exception If the header is not valid JSON.
     */
    public function searchHeader($header, $searchKey)
    {
        $parsedHeader = json_decode($this->parseHeader($header), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erreur de décodage JSON : " . json_last_error_msg());
        }

        $keys = explode('#~>', $searchKey);
        $mainKey = $keys[0];
        $subKey = isset($keys[1]) ? $keys[1] : null;

        if (isset($parsedHeader[$mainKey])) {
            if ($subKey) {
                $subKeys = explode('#~>', $subKey);
                $result = $parsedHeader[$mainKey];
                foreach ($subKeys as $key) {
                    if (is_array($result) && isset($result[$key])) {
                        $result = $result[$key];
                    } else {
                        return null;
                    }
                }
                return $result;
            } else {
                return $parsedHeader[$mainKey];
            }
        }

        return null;
    }

    /**
     * Checks if the given header is a valid KPF header.
     *
     * @param string $header The header to check.
     *
     * @return bool True if the header is valid, false otherwise.
     */
    public function checkHeader($header): bool 
    {
        $startPos = strpos($header, '#!!');
        $endPos = strpos($header, '~!!#');
    
        if ($startPos !== 0 || $endPos === false || $endPos < $startPos) {
            return false;
        }
    
        $headerContent = substr($header, $startPos + 3, $endPos - $startPos - 3); 
        return strpos($headerContent, '@kpfenc') !== false;
    }
}
