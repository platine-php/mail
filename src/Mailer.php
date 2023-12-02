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
 *  @file Mailer.php
 *
 *  The Mailer class
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

use Platine\Mail\Transport\NullTransport;
use Platine\Mail\Transport\TransportInterface;

/**
 * Class Mailer
 * @package Platine\Mail
 */
class Mailer
{
    /**
     * The mail transport instance
     * @var TransportInterface
     */
    protected TransportInterface $transport;

    /**
     * Create new instance
     * @param TransportInterface|null $transport
     */
    public function __construct(?TransportInterface $transport = null)
    {
        $this->transport = $transport ? $transport : new NullTransport();
    }

    /**
     * Get the transport instance
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * Set the transport instance
     * @param TransportInterface $transport
     * @return $this
     */
    public function setTransport(TransportInterface $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * Send the message
     * @param MessageInterface $message
     * @return bool
     */
    public function send(MessageInterface $message): bool
    {
        return $this->transport->send($message);
    }
}
