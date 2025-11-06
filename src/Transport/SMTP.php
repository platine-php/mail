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
 *  @file SMTP.php
 *
 *  The SMTP transport class
 *
 *  @package    Platine\Mail\Transport
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Mail\Transport;

use Platine\Mail\Exception\SMTPException;
use Platine\Mail\Exception\SMTPRetunCodeException;
use Platine\Mail\Exception\SMTPSecureException;
use Platine\Mail\MessageInterface;

/**
 * @class SMTP
 * @package Platine\Mail\Transport
 */
class SMTP implements TransportInterface
{
    /**
     * End of line char
     */
    protected const CRLF = "\r\n";

    /**
     * The SMTP socket instance
     * @var resource|bool
     */
    protected $smtp = null;

    /**
     * The SMTP host
     * @var string
     */
    protected string $host;

    /**
     * SMTP server port
     * @var int
     */
    protected int $port = 25;

    /**
     * Whether need use SSL connection
     * @var bool
     */
    protected bool $ssl = false;

    /**
     * Whether need use TLS connection
     * @var bool
     */
    protected bool $tls = false;

    /**
     * The username
     * @var string
     */
    protected string $username = '';

    /**
     * The password
     * @var string
     */
    protected string $password = '';

    /**
     * The instance of message to send
     * @var MessageInterface
     */
    protected MessageInterface $message;

    /**
     * List of all commands send to server
     * @var array<int, string>
     */
    protected array $commands = [];

    /**
     * List of all responses receive from server
     * @var array<int, string>
     */
    protected array $responses = [];

    /**
     * Connection timeout
     * @var int
     */
    protected int $timeout = 30;

    /**
     * Server response timeout
     * @var int
     */
    protected int $responseTimeout = 10;

    /**
     * Create new instance
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @param int $responseTimeout
     */
    public function __construct(
        string $host,
        int $port = 25,
        int $timeout = 30,
        int $responseTimeout = 10
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->responseTimeout = $responseTimeout;
    }

    /**
     *
     * @param int $timeout
     * @return $this
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     *
     * @param int $responseTimeout
     * @return $this
     */
    public function setResponseTimeout(int $responseTimeout): self
    {
        $this->responseTimeout = $responseTimeout;
        return $this;
    }


    /**
     * Set TLS connection
     * @param bool $status
     * @return $this
     */
    public function tls(bool $status = true): self
    {
        $this->tls = $status;

        return $this;
    }

    /**
     * Set SSL connection
     * @param bool $status
     * @return $this
     */
    public function ssl(bool $status = true): self
    {
        $this->ssl = $status;

        return $this;
    }

    /**
     * Set authentication information
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuth(string $username, string $password): self
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function send(MessageInterface $message): bool
    {
        $this->message = $message;

        $this->connect()
              ->ehlo();

        if ($this->tls) {
            $this->starttls()
                  ->ehlo();
        }

        $this->authLogin()
              ->mailFrom()
              ->rcptTo()
              ->data()
              ->quit();

        if (is_resource($this->smtp)) {
            return fclose($this->smtp);
        }

        return false;
    }

    /**
     * Return the list of commands send to server
     * @return array<int, string>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Return the list of responses from server
     * @return array<int, string>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

        /**
     * Connect to server
     * @return $this
     * @throws SMTPException
     * @throws SMTPRetunCodeException
     */
    protected function connect(): self
    {
        $host = $this->ssl ? 'ssl://' . $this->host : $this->host;
        $this->smtp = @fsockopen(
            $host,
            $this->port,
            $errorNumber,
            $errorMessage,
            $this->timeout
        );

        if (is_resource($this->smtp) === false) {
            throw new SMTPException(sprintf(
                'Could not establish SMTP connection to server [%s] error: [%s: %s]',
                $host,
                $errorNumber,
                $errorMessage
            ));
        }

        $code = $this->getCode();
        if ($code !== 220) {
            throw new SMTPRetunCodeException(220, $code, array_pop($this->responses));
        }

        return $this;
    }

    /**
     * Start TLS connection
     * @return $this
     * @throws SMTPRetunCodeException
     * @throws SMTPSecureException
     */
    protected function starttls(): self
    {
        $code = $this->sendCommand('STARTTLS');
        if ($code !== 220) {
            throw new SMTPRetunCodeException(220, $code, array_pop($this->responses));
        }

        /**
        * STREAM_CRYPTO_METHOD_TLS_CLIENT is quite the mess ...
        *
        * - On PHP <5.6 it doesn't even mean TLS, but SSL 2.0, and there's no option to use actual TLS
        * - On PHP 5.6.0-5.6.6, >=7.2 it means negotiation with any of TLS 1.0, 1.1, 1.2
        * - On PHP 5.6.7-7.1.* it means only TLS 1.0
        *
        * We want the negotiation, so we'll force it below ...
        */
        if (is_resource($this->smtp)) {
            if (
                !stream_socket_enable_crypto(
                    $this->smtp,
                    true,
                    STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT
                    | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                )
            ) {
                throw new SMTPSecureException('Start TLS failed to enable crypto');
            }
        }

        return $this;
    }

    /**
     * Send hello command
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function ehlo(): self
    {
        $command = 'EHLO ' . $this->host . self::CRLF;
        $code = $this->sendCommand($command);
        if ($code !== 250) {
            throw new SMTPRetunCodeException(250, $code, array_pop($this->responses));
        }

        return $this;
    }

    /**
     * Authentication to server
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function authLogin(): self
    {
        if (empty($this->username) && empty($this->password)) {
            return $this;
        }

        $command = 'AUTH LOGIN' . self::CRLF;
        $code = $this->sendCommand($command);
        if ($code !== 334) {
            throw new SMTPRetunCodeException(334, $code, array_pop($this->responses));
        }

        $command = base64_encode($this->username) . self::CRLF;
        $codeUsername = $this->sendCommand($command);
        if ($codeUsername !== 334) {
            throw new SMTPRetunCodeException(334, $codeUsername, array_pop($this->responses));
        }

        $command = base64_encode($this->password) . self::CRLF;
        $codePassword = $this->sendCommand($command);
        if ($codePassword !== 235) {
            throw new SMTPRetunCodeException(235, $codePassword, array_pop($this->responses));
        }

        return $this;
    }

    /**
     * Set From value
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function mailFrom(): self
    {
        $command = 'MAIL FROM:' . $this->message->getFrom() . self::CRLF;
        $code = $this->sendCommand($command);
        if ($code !== 250) {
            throw new SMTPRetunCodeException(250, $code, array_pop($this->responses));
        }

        return $this;
    }

    /**
     * Set recipients
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function rcptTo(): self
    {
        $recipients = array_merge(
            $this->message->getTo(),
            $this->message->getCc(),
            $this->message->getBcc()
        );

        foreach ($recipients as $email) {
            $command = 'RCPT TO:<' . $email . '>' . self::CRLF;
            $code = $this->sendCommand($command);
            if ($code !== 250) {
                throw new SMTPRetunCodeException(250, $code, array_pop($this->responses));
            }
        }

        return $this;
    }

    /**
     * Send mail data to server
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function data(): self
    {
        $command = 'DATA' . self::CRLF;
        $code = $this->sendCommand($command);
        if ($code !== 354) {
            throw new SMTPRetunCodeException(354, $code, array_pop($this->responses));
        }

        $command = (string) $this->message;
        $command .= self::CRLF . '.' . self::CRLF;
        $codeMessage = $this->sendCommand($command);
        if ($codeMessage !== 250) {
            throw new SMTPRetunCodeException(250, $codeMessage, array_pop($this->responses));
        }

        return $this;
    }

    /**
     * Disconnect from server
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function quit(): self
    {
        $command = 'QUIT' . self::CRLF;
        $code = $this->sendCommand($command);
        if ($code !== 221) {
            throw new SMTPRetunCodeException(221, $code, array_pop($this->responses));
        }

        return $this;
    }

    /**
     * Send command to server
     * @param string $command
     * @return int
     */
    protected function sendCommand(string $command): int
    {
        $this->commands[] = $command;
        if (is_resource($this->smtp)) {
            fputs($this->smtp, $command, strlen($command));
        }
        return $this->getCode();
    }

    /**
     * Get return code from server
     * @return int
     * @throws SMTPException
     */
    protected function getCode(): int
    {
        if (is_resource($this->smtp)) {
            stream_set_timeout($this->smtp, $this->responseTimeout);
            while ($str = fgets($this->smtp, 515)) {
                $this->responses[] = $str;

                if (substr($str, 3, 1) === ' ') {
                    $code = substr($str, 0, 3);
                    return (int) $code;
                }
            }
        }

        throw new SMTPException('SMTP Server did not respond with anything I recognized');
    }
}
