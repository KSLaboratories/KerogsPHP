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


    public function __construct(
    /**
     * Constructor method for Sendmail class.
     * 
     * @param string $from The sender's email address.
     * @param string $customHeader The custom header for the email. If not specified, a default header will be used.
     * @param string $contentType The content type of the email. If not specified, a default content type of 'text/html' will be used.
     * 
     * @throws \Exception If the sender's email address is not valid.
     */
        string $from = "",
        string $customHeader = null,
        string $contentType = null
    ) {
        if(!$from){
            throw new \Exception("need a 'from' address");
        } else{
            if(!filter_var($from, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("invalid 'from' address");
            } else{
                $this->from = $from;
            }
        }

        if($customHeader !== null) {
            $this->customHeader = $customHeader;
        } else{

            if($contentType !== null) {
                $this->$customHeader = "Content-Type: ".$contentType."; charset=utf-8\r\n";
            } else{
                $this->$customHeader = "Content-Type: 'text/html'; charset=utf-8\r\n";
            }

            $this->$customHeader .= "From: " . $this->from."\r\n";
        }
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
    public function sendMail($to, $subject, $message):bool {
        if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("invalid 'to' address");
        } else{
            return mail($to, $subject, $message, $this->customHeader);   
        }
    }
}
