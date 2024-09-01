<?php
/*
 * (c) Kerogs kerogs.labs@gmail.com
 *
 * This source file is subject to the Mozilla Public License Version 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kerogs\KerogsPhp;

class Logs
{
    private $encryptionKey;
    private $encryptFile;

    /**
     * Constructor method for Logs class.
     * 
     * @param string $encryptionKey Key for encryption/decryption.
     * @param bool $encryptFile Whether to encrypt the log file (default is false).
     */
    public function __construct(
        string $encryptionKey = '',
        bool $encryptFile = false
    ) {
        $this->encryptionKey = $encryptionKey;
        $this->encryptFile = $encryptFile;
    }

    /**
     * Add a log entry to the file.
     *
     * @param string|null $pathLogs Path to the log file (default is the root of the site + "/kp_server.log").
     * @param string $message Log message.
     * @param int $statusCode HTTP status code.
     * @param string $logType Log type (e.g., INFO, ERROR).
     * @param bool $logIp Whether to log the IP address (default is false).
     * @param bool $logRequestData Whether to log GET/POST data (default is false).
     * @return bool Returns true if the log entry was successfully added, false otherwise.
     */
    public function addLog(
        string $pathLogs = null,
        string $message = "-",
        int $statusCode = 200,
        string $logType = "INFO",
        bool $logIp = false,
        bool $logRequestData = false
    ): bool {
        // Si $pathLogs est null, on définit un chemin par défaut
        if ($pathLogs === null) {
            $pathLogs = $_SERVER['DOCUMENT_ROOT'] . "/kp_server.log";
        }

        // Si le fichier est crypté, on le décrypte d'abord
        $isEncrypted = $this->encryptFile && file_exists($pathLogs . '.kpc');
        if ($isEncrypted) {
            $this->encryptDecryptFile($pathLogs . '.kpc', false); // Décrypte vers le fichier .log
        }

        // Récupération de l'identifiant unique et du timestamp
        $uniqid = uniqid();
        $timestamp = (new \DateTime())->format("Y-m-d H:i:s.u");

        // Log IP conditionnel
        $ipv4 = ($logIp && isset($_SERVER['REMOTE_ADDR']) && $this->isValidIpAddress($_SERVER['REMOTE_ADDR']))
            ? $_SERVER['REMOTE_ADDR']
            : "-";

        // Protocole et méthode HTTP
        $httpMethod = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'HTTPS' : 'HTTP';
        $protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
        $pathShow = isset($_SERVER['REQUEST_URI']) ? $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : "-";
        $pathReal = isset($_SERVER['SCRIPT_NAME']) ? $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] : "-";

        // Récupération des données GET/POST selon le paramètre $logRequestData
        $requestData = $logRequestData ? $this->getRequestData() : "-";

        // Création du contenu du log
        $contentToAdd = "[$statusCode] [$uniqid] $timestamp $ipv4 [$logType] $message [$httpMethod] [$pathShow] ($pathReal) $requestData";

        // Ajout du log au fichier
        $logAdded = $this->prependToFile($pathLogs, $contentToAdd);

        // Cryptage du fichier si demandé et log ajouté avec succès
        if ($logAdded && $this->encryptFile && !empty($this->encryptionKey)) {
            $this->encryptDecryptFile($pathLogs, true); // Recrypte le fichier en .kpc
        }

        return $logAdded;
    }


    /**
     * Encrypt or decrypt a log file.
     *
     * @param string $filePath Path to the log file.
     * @param bool $encrypt Whether to encrypt (true) or decrypt (false) the file.
     */
    public function encryptDecryptFile(string $filePath, bool $encrypt): void
    {
        $method = 'aes-256-cbc';
        $ivLength = openssl_cipher_iv_length($method);
        $iv = substr($this->encryptionKey, 0, $ivLength);

        // Construction des chemins de fichier
        if ($encrypt) {
            if (substr($filePath, -4) !== '.log') {
                echo "Error: Only '.log' files can be encrypted.";
                return;
            }
            $inputFilePath = $filePath;
            $outputFilePath = $filePath . '.kpc';
        } else {
            if (substr($filePath, -4) !== '.kpc') {
                echo "Error: Only '.kpc' files can be decrypted.";
                return;
            }
            $inputFilePath = $filePath;
            $outputFilePath = substr($filePath, 0, -4); // Enlève '.kpc'
        }

        // Vérification de l'existence du fichier source
        if (!file_exists($inputFilePath)) {
            echo "Error: File does not exist - $inputFilePath";
            return;
        }

        $content = file_get_contents($inputFilePath);
        if ($content === false) {
            echo "Error: Unable to read file - $inputFilePath";
            return;
        }

        if ($encrypt) {
            $encrypted = openssl_encrypt($content, $method, $this->encryptionKey, 0, $iv);
            if ($encrypted === false || file_put_contents($outputFilePath, $encrypted) === false) {
                echo "Error: Unable to write encrypted file - $outputFilePath";
                return;
            }
        } else {
            $decrypted = openssl_decrypt($content, $method, $this->encryptionKey, 0, $iv);
            if ($decrypted === false || file_put_contents($outputFilePath, $decrypted) === false) {
                echo "Error: Unable to write decrypted file - $outputFilePath";
                return;
            }
        }

        // Supprimer le fichier d'entrée après une opération réussie
        if (file_exists($outputFilePath)) {
            unlink($inputFilePath); // Supprime l'ancien fichier crypté après cryptage ou décryptage réussi
        }
    }





    /**
     * Get the request data based on the HTTP method (GET, POST, PUT, DELETE, etc.).
     *
     * @return string The request data in JSON format or "-" if no data is present.
     */
    private function getRequestData(): string
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $data = !empty($_GET) ? $_GET : '-';
                break;

            case 'POST':
                $data = !empty($_POST) ? $_POST : '-';
                break;

            case 'PUT':
            case 'DELETE':
                // Récupération des données PUT/DELETE
                $inputData = file_get_contents('php://input');
                parse_str($inputData, $data);
                $data = !empty($data) ? $data : '-';
                break;

            default:
                $data = '-';
                break;
        }

        // Si des données sont présentes, on les encode en JSON
        return $data !== '-' ? json_encode($data) : '-';
    }

    /**
     * Checks if the given IP address is valid.
     *
     * @param string $ip The IP address to validate.
     * @return bool Returns true if the IP address is valid, false otherwise.
     */
    private function isValidIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Prepends new content to the beginning of a file.
     *
     * @param string $filePath The path to the file.
     * @param string $newContent The content to be prepended.
     * @return bool Returns true if the content was successfully prepended, false otherwise.
     */
    private function prependToFile(string $filePath, string $newContent): bool
    {
        // Crée le fichier s'il n'existe pas
        if (!file_exists($filePath)) {
            $handle = fopen($filePath, 'w');
            if ($handle === false) {
                return false;
            }
            fclose($handle);
        }

        $currentContent = file_get_contents($filePath);
        $file = fopen($filePath, 'w');
        if ($file === false) {
            return false;
        }
        fwrite($file, $newContent . PHP_EOL);
        fwrite($file, $currentContent);
        fclose($file);
        return true;
    }
}
