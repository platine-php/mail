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
 *  @file File.php
 *
 *  The file transport class
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

use Platine\Mail\Exception\FileTransportException;
use Platine\Mail\MessageInterface;

/**
 * @class File
 * @package Platine\Mail\Transport
 */
class File implements TransportInterface
{
    /**
     *
     * @var string
     */
    protected string $path;

    /**
     * Create new instance
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        if ($path === null) {
            $path = sys_get_temp_dir();
        }
        $this->path = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritedoc}
     */
    public function send(MessageInterface $message): bool
    {
        if (!is_dir($this->path) || !is_writable($this->path)) {
            throw new FileTransportException(sprintf(
                'The message destination directory [%s] does '
                    . 'not exist or is not writeable',
                $this->path
            ));
        }

        $content = (string)$message;
        $file = $this->path . date('YmdHis') . '-' . md5(uniqid()) . '.txt';
        if (!file_put_contents($file, $content)) {
            throw new FileTransportException(sprintf(
                'Could not write message to file [%s]',
                $file
            ));
        }

        return true;
    }
}
