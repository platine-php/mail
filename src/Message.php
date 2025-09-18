<?php

/**
 * Platine Mail
 *
 * Platine Mail provides a flexible and powerful PHP email sender
 *  with support of SMTP, Native Mail, sendmail, etc transport.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Mail
 * Copyright (c) 2015, Sonia Marquette
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file Message.php
 *
 *  The Message class
 *
 *  @package    Platine\Mail
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Mail;

use InvalidArgumentException;

/**
 * @class Message
 * @package Platine\Mail
 */
class Message implements MessageInterface
{
    /**
     * End of line char
     */
    public const CRLF = PHP_EOL;

    /**
     *
     * @var string
     */
    protected string $from = '';

    /**
     *
     * @var string
     */
    protected string $replyTo = '';

    /**
     * The send mail receiver(s)
     * @var array<int, string>
     */
    protected array $to = [];

    /**
     * The send mail receiver(s) copy
     * @var array<int|string, string> $cc
     */
    protected array $cc = [];

    /**
     * The send mail receiver(s) hidden copy
     * @var array<int|string, string> $bcc
     */
    protected array $bcc = [];

    /**
     * The mail subject
     * @var string
     */
    protected string $subject = '';

    /**
     * The mail body
     * @var string
     */
    protected string $body = '';

     /**
     * The mail attachments
     * @var array<int, array<string, string>>
     */
    protected array $attachments = [];

     /**
     * The mail headers
     * @var array<string, mixed>
     */
    protected array $headers = [];

    /**
     * The mail boundary value
     * @var string
     */
    protected string $uid = '';

    /**
     * Maximum characters for each message line
     * @var int
     */
    protected int $wrap = 70;

    /**
     * Set mail priority
     * @var int
     */
    protected int $priority = 3;

    /**
     * Create new instance
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * {@inheritedoc}
     */
    public function reset(): self
    {
        $this->from = '';
        $this->replyTo = '';
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->subject = '';
        $this->body = '';
        $this->attachments = [];
        $this->headers = [];
        $this->uid = md5(uniqid((string)time()));
        $this->wrap = 70;
        $this->priority = 3;

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function addAttachment(string $path, ?string $filename = null): self
    {
        if (file_exists($path) === false) {
            throw new InvalidArgumentException(sprintf(
                'The email attachment file [%s] does not exists.',
                $path
            ));
        }

        if (empty($filename)) {
            $filename = basename($path);
        }

        $data = $this->getAttachmentData($path);

        if ($data !== null) {
            $this->attachments[] = [
                'file' => $this->encodeUtf8($this->filterString($filename)),
                'path' => $path,
                'data' => chunk_split(base64_encode($data))
            ];
        }

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function setBcc(array $pairs): self
    {
        $this->bcc = $pairs;

        return $this->addMailHeaders('Bcc', $pairs);
    }

    /**
     * {@inheritedoc}
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * {@inheritedoc}
     */
    public function setCc(array $pairs): self
    {
        $this->cc = $pairs;

        return $this->addMailHeaders('Cc', $pairs);
    }

    /**
     * {@inheritedoc}
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * {@inheritedoc}
     */
    public function setBody(string $body): self
    {
        $this->body = str_replace("\n.", "\n..", $body);

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function getEncodedBody(): string
    {
        $body = wordwrap($this->body, $this->wrap);
        if ($this->hasAttachments()) {
            $body = $this->getBodyWithAttachments();
        }

        return $body;
    }

    /**
     * {@inheritedoc}
     */
    public function getEncodedHeaders(): string
    {
        $this->prepareHeaders();

        $content = '';
        foreach ($this->headers as $name => $value) {
            $content .= $name . ': ' . $value . self::CRLF;
        }

        return $content;
    }

    /**
     * {@inheritedoc}
     */
    public function setFrom(string $email, ?string $name = null): self
    {
        $this->from = $this->formatHeader($email, $name);

        return $this->addMailHeader('From', $email, $name);
    }

    /**
     * {@inheritedoc}
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
    *  {@inheritedoc}
    */
    public function setReplyTo(string $email, ?string $name = null): self
    {
        $this->replyTo = $this->formatHeader($email, $name);

        return $this->addMailHeader('Reply-To', $email, $name);
    }

    /**
     * {@inheritedoc}
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * {@inheritedoc}
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $this->encodeUtf8($this->filterString($subject));

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function setTo(string $email, ?string $name = null): self
    {
        $this->to[] = $this->formatHeader($email, $name);

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function getEncodedTo(): string
    {
        return join(', ', $this->to);
    }

    /**
     * {@inheritedoc}
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * {@inheritedoc}
     */
    public function setWrap(int $wrap = 70): self
    {
        if ($wrap < 1) {
            $wrap = 70;
        }

        $this->wrap = $wrap;

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function setPriority(int $priority = 3): self
    {
        if ($priority < 1 || $priority > 5) {
            $priority = 3;
        }

        $this->priority = $priority;

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function getHeader(string $name, mixed $default = null): mixed
    {
        $this->prepareHeaders();

        return array_key_exists($name, $this->headers)
                ? $this->headers[$name]
                : $default;
    }

    /**
     * {@inheritedoc}
     */
    public function addHeader(string $name, mixed $value): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function addMailHeader(string $header, string $email, ?string $name = null): self
    {
        $address = $this->formatHeader($email, $name);
        $this->headers[$header] = $address;

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function addMailHeaders(string $header, array $pairs): self
    {
        if (count($pairs) === 0) {
            throw new InvalidArgumentException('The mail headers is empty');
        }

        $addresses = [];
        foreach ($pairs as $name => $email) {
            if (is_numeric($name)) {
                $name = null;
            }
            $addresses[] = $this->formatHeader($email, $name);
        }

        $this->addHeader($header, implode(', ', $addresses));

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * {@inheritedoc}
     */
    public function setHtml(): self
    {
        return $this->addHeader('Content-Type', 'text/html; charset="UTF-8"');
    }

    /**
     * {@inheritedoc}
     */
    public function __toString(): string
    {
        $content = $this->getEncodedHeaders();
        $content .= self::CRLF;
        $content .= $this->getEncodedBody();

        return $content;
    }

    /**
     * Prepare mail headers
     * @return $this
     */
    protected function prepareHeaders(): self
    {
        if (!array_key_exists('Return-Path', $this->headers)) {
            $this->addHeader('Return-Path', $this->from);
        }

        if (!array_key_exists('Reply-To', $this->headers)) {
            $this->addHeader('Reply-To', $this->from);
        }

        $this->addHeader('X-Priority', $this->priority)
               ->addHeader('X-Mailer', 'Platine PHP Mail')
               ->addHeader('Subject', $this->subject)
               ->addHeader('To', join(', ', $this->to))
               ->addHeader('Date', date('r'));

        if ($this->hasAttachments()) {
            $this->addHeader('MIME-Version', '1.0')
                 ->addHeader(
                     'Content-Type',
                     sprintf('multipart/mixed; boundary="%s"', $this->uid)
                 );
        }

        return $this;
    }

    /**
     * Get mail attachment data
     * @param string $path
     *
     * @return string|null
     */
    protected function getAttachmentData(string $path): ?string
    {
        $filesize = filesize($path);
        if ($filesize === false || $filesize < 1) {
            return null;
        }

        $handle = fopen($path, 'r');
        $content = null;
        if (is_resource($handle)) {
            $result = fread($handle, $filesize);
            if ($result !== false) {
                $content = $result;
            }
            fclose($handle);
        }

        return $content;
    }

    /**
     * Return the attachment with body
     * @return string
     */
    protected function getBodyWithAttachments(): string
    {
        $body = [];
        $body[] = 'This is a multi-part message in MIME format.';
        $body[] = sprintf('--%s', $this->uid);
        $body[] = 'Content-Type: text/html; charset="UTF-8"';
        $body[] = 'Content-Transfer-Encoding: base64';
        $body[] = self::CRLF;
        $body[] = chunk_split(base64_encode($this->body));
        $body[] = self::CRLF;
        $body[] = sprintf('--%s', $this->uid);

        foreach ($this->attachments as $attachment) {
            $body[] = $this->getAttachmentMimeTemplate($attachment);
        }

        return implode(self::CRLF, $body) . '--';
    }

    /**
     * Get attachment mime template
     * @param array<string, string> $attachment
     * @return string
     */
    protected function getAttachmentMimeTemplate(array $attachment): string
    {
        $file = $attachment['file'];
        $data = $attachment['data'];

        $head = [];
        $head[] = sprintf('Content-Type: application/octet-stream; name="%s"', $file);
        $head[] = 'Content-Transfer-Encoding: base64';
        $head[] = sprintf('Content-Disposition: attachment; filename="%s"', $file);
        $head[] = '';
        $head[] = $data;
        $head[] = '';
        $head[] = sprintf('--%s', $this->uid);

        return implode(self::CRLF, $head);
    }

    /**
     * Format mail header
     * @param string $email
     * @param string|null $name
     * @return string
     */
    protected function formatHeader(string $email, ?string $name = null): string
    {
        if (empty($name)) {
            return $email;
        }

        return sprintf(
            '"%s" <%s>',
            $this->encodeUtf8($this->filterName($name)),
            $this->filterEmail($email)
        );
    }

    /**
     * Filter email address
     * @param string $email
     * @return string
     */
    protected function filterEmail(string $email): string
    {
        $rules = [
           "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => '',
            ','  => '',
            '<'  => '',
            '>'  => ''
        ];

        $emailFiltered = filter_var(
            strtr($email, $rules),
            FILTER_SANITIZE_EMAIL
        );

        return $emailFiltered === false ? '' : $emailFiltered;
    }

    /**
     * Filter name address
     * @param string $name
     * @return string
     */
    protected function filterName(string $name): string
    {
        $rules = [
           "\r" => '',
            "\n" => '',
            "\t" => '',
            '"'  => "'",
            '<'  => '[',
            '>'  => ']',
        ];

        $filtered = filter_var(
            $name,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_NO_ENCODE_QUOTES
        );

        if ($filtered === false) {
            return '';
        }

        return trim(strtr($filtered, $rules));
    }

    /**
     * Filter the string other than email and name
     * @param string $value
     * @return string
     */
    protected function filterString(string $value): string
    {
        $filtered = filter_var(
            $value,
            FILTER_UNSAFE_RAW,
            FILTER_FLAG_STRIP_LOW
        );
        return $filtered === false ? '' : $filtered;
    }

    /**
     * Encode the UTF-8 value for the given string
     * @param string $value
     * @return string
     */
    protected function encodeUtf8(?string $value): string
    {
        $valueClean = trim((string)$value);
        if (preg_match('/(\s)/', $valueClean)) {
            return $this->encodeUtf8Words($valueClean);
        }

        return $this->encodeUtf8Word($valueClean);
    }

    /**
     * Encode the UTF-8 value for on word
     * @param string $value
     * @return string
     */
    protected function encodeUtf8Word(string $value): string
    {
        return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
    }

    /**
     * Encode the UTF-8 for multiple word
     * @param string $value
     * @return string
     */
    protected function encodeUtf8Words(string $value): string
    {
        $words = explode(' ', $value);
        $encoded = [];
        foreach ($words as $word) {
            $encoded[] = $this->encodeUtf8Word($word);
        }
        return join($this->encodeUtf8Word(' '), $encoded);
    }
}
