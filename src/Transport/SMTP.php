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
     * Encryption values
     */
    public const ENCRYPTION_NONE = 'none';
    public const ENCRYPTION_STARTTLS = 'starttls';
    public const ENCRYPTION_TLS = 'tls';

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
     * The encryption
     * @var string
     */
    protected string $encryption = self::ENCRYPTION_NONE;

    /**
     * The instance of message to send
     * @var MessageInterface
     */
    protected MessageInterface $message;

    /**
     * Log of messages between client and server
     * @var array<array<string, string>>
     */
    protected array $logs = [];

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
     * Set the encryption
     * @param string $encryption
     * @return $this
     */
    public function setEncryption(string $encryption): self
    {
        $values = [
            self::ENCRYPTION_NONE,
            self::ENCRYPTION_STARTTLS,
            self::ENCRYPTION_TLS
        ];

        if (in_array($encryption, $values) === false) {
            throw new SMTPException(sprintf(
                'Invalid encryption value [%s], must be one of [%s]',
                $encryption,
                implode(', ', $values)
            ));
        }

        $this->encryption = $encryption;
        return $this;
    }

    /**
     * {@inheritedoc}
     */
    public function send(MessageInterface $message): bool
    {
        $this->message = $message;

        $this->connect()
             ->authLogin()
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
     * Return the list of messages from client and server
     * @return array<array<string, string>>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Return the debug information
     * @return string
     */
    public function debugInfo(): string
    {
        $messages = [];
        foreach ($this->logs as $log) {
            $messages[] = sprintf('%s: %s', $log['type'], $log['message']);
        }
        return implode("\n", $messages);
    }

    /**
     * Enable support of TLS for the socket
     * @return $this
     * @throws SMTPSecureException
     */
    protected function enableTls(): self
    {
        if (
            !stream_socket_enable_crypto(
                $this->smtp,
                true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            )
        ) {
            throw new SMTPSecureException('Start TLS failed to enable crypto');
        }

        return $this;
    }

    /**
     * Connect to SMTP server
     * @return $this
     * @throws SMTPException
     * @throws SMTPRetunCodeException
     */
    protected function connect(): self
    {
        $this->smtp = @fsockopen(
            $this->host,
            $this->port,
            $errorNumber,
            $errorMessage,
            $this->timeout
        );

        if (is_resource($this->smtp) === false) {
            throw new SMTPException(sprintf(
                'Could not establish SMTP connection to server [%s:%d] error: [%s: %s]',
                $this->host,
                $this->port,
                $errorNumber,
                $errorMessage
            ));
        }

        switch ($this->encryption) {
            case self::ENCRYPTION_TLS:
                $this->enableTls();
                $code = $this->getCode();
                $this->checkReturnCode(220, $code);
                $this->ehlo();
                break;
            case self::ENCRYPTION_STARTTLS:
                $code = $this->getCode();
                $this->checkReturnCode(220, $code);
                $this->ehlo()
                     ->starttls()
                     ->enableTls()
                     ->ehlo();
                break;
            case self::ENCRYPTION_NONE:
                $code = $this->getCode();
                $this->checkReturnCode(220, $code);
                $this->ehlo();
                break;
        }

        return $this;
    }

    /**
     * Start TLS connection
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function starttls(): self
    {
        $code = $this->sendCommand('STARTTLS');
        $this->checkReturnCode(220, $code);

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
        $this->checkReturnCode(250, $code);

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
        $this->checkReturnCode(334, $code);


        $command = base64_encode($this->username) . self::CRLF;
        $codeUsername = $this->sendCommand($command);
        $this->checkReturnCode(334, $codeUsername);

        $command = base64_encode($this->password) . self::CRLF;
        $codePassword = $this->sendCommand($command);
        $this->checkReturnCode(235, $codePassword);

        return $this;
    }

    /**
     * Set From value
     * @return $this
     * @throws SMTPRetunCodeException
     */
    protected function mailFrom(): self
    {
        $command = 'MAIL FROM:<' . $this->message->getFromEmail() . '>' . self::CRLF;
        $code = $this->sendCommand($command);
        $this->checkReturnCode(250, $code);

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
            $this->checkReturnCode(250, $code);
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
        $this->checkReturnCode(354, $code);

        $command = (string) $this->message;
        $command .= self::CRLF . '.' . self::CRLF;
        $codeMessage = $this->sendCommand($command);
        $this->checkReturnCode(250, $codeMessage);

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
        $this->checkReturnCode(221, $code);

        return $this;
    }

    /**
     * Send command to server
     * @param string $command
     * @return int
     */
    protected function sendCommand(string $command): int
    {
        $this->logs[] = ['type' => 'C', 'message' => rtrim($command)];
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
        $code = -1;
        if (is_resource($this->smtp)) {
            stream_set_timeout($this->smtp, $this->responseTimeout);
            $response = '';

            while ($str = fgets($this->smtp, 515)) {
                $response .= $str;

                if (substr($str, 3, 1) === ' ') {
                    $code = (int) substr($str, 0, 3);
                    break;
                }
            }
            $this->logs[] = [
                'type' => 'S',
                'message' => rtrim($response)
            ];
        }

        if ($code === -1) {
            throw new SMTPException('SMTP Server did not respond with anything I recognized');
        }

        return $code;
    }

    /**
     * Return the last server response message
     * @return string
     */
    protected function getLastServerResponse(): string
    {
        $log = end($this->logs);
        reset($this->logs);
        if ($log !== false && isset($log['type']) && $log['type'] === 'S') {
            return $log['message'];
        }

        return '';
    }

    /**
     * Check the return code against the expected code
     * @param int $expected
     * @param int $responseCode
     * @return void
     * @throws SMTPRetunCodeException
     */
    protected function checkReturnCode(int $expected, int $responseCode): void
    {
        if ($responseCode !== $expected) {
            throw new SMTPRetunCodeException(
                $expected,
                $responseCode,
                $this->getLastServerResponse()
            );
        }
    }
}
