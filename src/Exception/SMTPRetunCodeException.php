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

declare(strict_types=1);

namespace Platine\Mail\Exception;

/**
 * @class SMTPRetunCodeException
 * @package Platine\Mail\Exception
 */
class SMTPRetunCodeException extends SMTPException
{
    /**
     * Create new instance
     * @param int $expected
     * @param int $received
     * @param string|null $serverMessage
     */
    public function __construct(int $expected, int $received, ?string $serverMessage = null)
    {
        $message = sprintf(
            'Unexpected return code expected %d, but got %d',
            $expected,
            $received
        );

        if ($serverMessage !== null) {
            $message .= ' | ' . $serverMessage;
        }
        parent::__construct($message);
    }
}
