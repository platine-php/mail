<?php

/**
 * Platine Mail
 *
 * Platine Mail provides a flexible and powerful PHP email sender
 * with support of SMTP, Native Mail, sendmail, etc transport.
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
 *  @file MessageInterface.php
 *
 *  The Mail message representation Interface
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


/**
 * @class MessageInterface
 * @package Platine\Mail
 */
interface MessageInterface
{
    /**
     * Set sender
     * @param string $email
     * @param string|null $name
     * @return self
     */
    public function setFrom(string $email, ?string $name = null): self;

    /**
     * Return the value of sender
     * @return string
     */
    public function getFrom(): string;

    /**
     * Set reply information
     * @param string $email
     * @param string|null $name
     * @return self
     */
    public function setReplyTo(string $email, ?string $name = null): self;

    /**
     *
     * @param string $email
     * @param string|null $name
     * @return self
     */
    public function setTo(string $email, ?string $name = null): self;

    /**
     *
     * @return array<int|string, string>
     */
    public function getTo(): array;

    /**
     * Return the encoded receivers ready to send
     * @return string
     */
    public function getEncodedTo(): string;

    /**
     * Set copy recipients
     * @param array<int|string, string> $pairs
     * @return self
     */
    public function setCc(array $pairs): self;

    /**
     *
     * @return array<int|string, string>
     */
    public function getCc(): array;

    /**
     * Set hidden copy recipients
     * @param array<int|string, string> $pairs
     * @return self
     */
    public function setBcc(array $pairs): self;

    /**
     *
     * @return array<int|string, string>
     */
    public function getBcc(): array;

    /**
     * Set each line maximum characters
     * @param int $wrap
     * @return self
     */
    public function setWrap(int $wrap = 78): self;

    /**
     * Set mail priority
     * @param int $priority
     * @return self
     */
    public function setPriority(int $priority = 3): self;

    /**
     * Set content type to "HTML"
     * @return self
     */
    public function setHtml(): self;

    /**
     * Set mail subject
     * @param string $subject
     * @return self
     */
    public function setSubject(string $subject): self;

    /**
     * Return the mail subject
     * @return string
     */
    public function getSubject(): string;

    /**
     * Set mail body
     * @param string $body
     * @return self
     */
    public function setBody(string $body): self;

    /**
     * Return the encoded body ready to send
     * @return string
     */
    public function getEncodedBody(): string;

    /**
     * The string representation of this message
     * @return string
     */
    public function __toString(): string;

    /**
     * Add mail attachment
     * @param string $path
     * @param string|null $filename if null will use the base name of the file
     * @return self
     */
    public function addAttachment(string $path, ?string $filename = null): self;

    /**
     * Whether the mail has attachment
     * @return bool
     */
    public function hasAttachments(): bool;

    /**
     * Return the value of the header
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getHeader(string $name, mixed $default = null): mixed;

    /**
     * Add mail header
     * @param string $header
     * @param string $email
     * @param string|null $name
     * @return self
     */
    public function addMailHeader(string $header, string $email, ?string $name = null): self;

    /**
     * Add mail generic header
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function addHeader(string $name, mixed $value): self;

    /**
     * Add mail headers
     * @param string $name
     * @param array<int|string, string> $pairs
     * @return self
     */
    public function addMailHeaders(string $name, array $pairs): self;

    /**
     * Reset the message to initial state
     * @return self
     */
    public function reset(): self;

    /**
     * Return the headers ready for sent
     * @return string
     */
    public function getEncodedHeaders(): string;
}
