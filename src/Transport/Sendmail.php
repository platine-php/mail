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
 *  @file Sendmail.php
 *
 *  The sendmail transport class
 *
 *  @package    Platine\Mail\Transport
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Mail\Transport;

use Platine\Mail\Exception\MailException;
use Platine\Mail\MessageInterface;

/**
 * Class Sendmail
 * @package Platine\Mail\Transport
 */
class Sendmail implements TransportInterface
{

    /**
     *
     * @var string
     */
    protected string $path;

    /**
     * Create new instance
     * @param string $path
     */
    public function __construct(string $path = '/usr/sbin/sendmail')
    {
        $this->path = $path;
    }

    /**
     * {@inheritedoc}
     */
    public function send(MessageInterface $message): bool
    {
        $content = (string)$message;

        $from = $message->getFrom();

        if (
            !function_exists('popen')
            || ($fp = @popen($this->path . ' -oi -f ' . $from . ' -t -r ' . $from, 'w')) === false
        ) {
            // server probably has popen disabled, so nothing we can do to get a verbose error.
            throw new MailException(
                'The message could not be delivered using sendmail. '
                    . 'The function popen() is disabled.'
            );
        }

        fputs($fp, $message->getFormattedHeaders());
        fputs($fp, $content);

        $status = pclose($fp);

        if ($status !== 0) {
            throw new MailException(sprintf(
                'Cannot open a socket to Sendmail. Check settings. Status code: [%d]',
                $status
            ));
        }

        return true;
    }
}
