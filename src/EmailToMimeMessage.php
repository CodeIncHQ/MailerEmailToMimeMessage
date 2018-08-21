<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2018 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material is strictly forbidden unless prior    |
// | written permission is obtained from Code Inc. SAS.                  |
// +---------------------------------------------------------------------+
//
// Author:   Joan Fabrégat <joan@codeinc.fr>
// Date:     10/08/2018
// Project:  Mailer
//
declare(strict_types=1);
namespace CodeInc\MailerEmailToMimeMessage;
use CodeInc\Mailer\Interfaces\EmailInterface;
use Zend\Mail\AddressList;
use Zend\Mime\Mime;


/**
 * Class EmailToMimeMessage
 *
 * @package CodeInc\MailerEmailToMimeMessage
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class EmailToMimeMessage
{
    /**
     * @var EmailInterface
     */
    private $email;

    /**
     * EmailToMimeMessage constructor.
     *
     * @param EmailInterface $email
     */
    public function __construct(EmailInterface $email)
    {
        $this->email = $email;
    }

    /**
     * @return \Zend\Mail\Message
     */
    public function getMimeMessage():\Zend\Mail\Message
    {
        $message = new \Zend\Mail\Message();
        $message->setEncoding($this->email->getCharset());
        $message->setSubject($this->email->getSubject());
        $message->addFrom($this->email->getSender());
        $to = new AddressList();
        foreach ($this->email->getRecipients() as $recipient) {
            $to->add($recipient->getAddress(), $recipient->getName());
        }
        $message->setTo($to);
        $message->setBody($this->getMimeBody());
        return $message;
    }

    /**
     * @return \Zend\Mime\Message
     */
    public function getMimeBody():\Zend\Mime\Message
    {
        return (new \Zend\Mime\Message())->setParts($this->getMimeParts());
    }

    /**
     * @return \Zend\Mime\Part[]
     */
    public function getMimeParts():array
    {
        $mimeParts = [];

        if ($this->email->getTextBody() !== null) {
            $textBodyPart = new \Zend\Mime\Part($this->email->getTextBody());
            $textBodyPart->setType(Mime::TYPE_TEXT);
            $textBodyPart->setCharset($this->email->getCharset());
            $textBodyPart->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
            $mimeParts[] = $textBodyPart;
        }

        if ($this->email->getHtmlBody() !== null) {
            $htmlBodyPart = new \Zend\Mime\Part($this->email->getHtmlBody());
            $htmlBodyPart->setType(Mime::TYPE_HTML);
            $htmlBodyPart->setCharset($this->email->getCharset());
            $mimeParts[] = $htmlBodyPart;
        }

        foreach ($this->email->getAttachments() as $attachment) {
            $part = new \Zend\Mime\Part();
            $part->setContent($attachment->getData());
            $part->setType($attachment->getMimeType());
            $part->setEncoding(Mime::ENCODING_BASE64);
            $part->setDisposition(Mime::DISPOSITION_ATTACHMENT);
            $part->setFileName($attachment->getFileName());
            $mimeParts[] = $part;
        }

        return $mimeParts;
    }
}