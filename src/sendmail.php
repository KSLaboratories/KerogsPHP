<?php
/*
 * (c) Kerogs kerogs.labs@gmail.com
 *
 * This source file is subject to the Mozilla Public License Version 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kerogs\KerogsPhp;

class Sendmail {

    private $from;
    private $customHeader;
    private $contentType;

    /**
     * Constructor method for Sendmail class.
     * 
     * @param string $from The sender's email address.
     * @param string|null $customHeader The custom header for the email. If not specified, a default header will be used.
     * @param string|null $contentType The content type of the email. If not specified, a default content type of 'text/html' will be used.
     * 
     * @throws \Exception If the sender's email address is not valid.
     */
    public function __construct(
        string $from,
        string $customHeader = null,
        string $contentType = null
    ) {
        if (empty($from)) {
            throw new \Exception("Sender's email address is required.");
        }

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid sender's email address.");
        }

        $this->from = $from;

        // Set content type
        $this->contentType = $contentType ?? 'text/html';

        // Build headers
        $this->customHeader = $customHeader ?? "Content-Type: " . $this->contentType . "; charset=utf-8\r\n";
        $this->customHeader .= "From: " . $this->from . "\r\n";
    }

    /**
     * Sends an email using the built-in mail() function.
     * 
     * @param string $to The recipient's email address.
     * @param string $subject The subject of the email.
     * @param string $message The content of the email.
     * 
     * @return bool Returns true if the email was sent successfully, false otherwise.
     * 
     * @throws \Exception If the recipient's email address is not valid.
     */
    public function sendMail(string $to, string $subject, string $message): bool {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid recipient's email address.");
        }

        return mail($to, $subject, $message, $this->customHeader);
    }
}
