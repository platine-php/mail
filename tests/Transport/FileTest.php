<?php

declare(strict_types=1);

namespace Platine\Test\File\Transport;

use Platine\Mail\Exception\FileTransportException;
use Platine\Mail\Exception\MailException;
use Platine\Mail\Message;
use Platine\Mail\Transport\File;
use Platine\PlatineTestCase;

/**
 * File class tests
 *
 * @group core
 * @group mail
 * @group transport
 */
class FileTest extends PlatineTestCase
{

    public function testSendDirectoryDoesNotExist(): void
    {
        global $mock_is_dir_to_false;

        $mock_is_dir_to_false = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new File();
        $this->expectException(FileTransportException::class);
        $e->send($message);
    }

    public function testSendDirectoryDoesNotWritable(): void
    {
        global $mock_is_dir_to_true,
                $mock_is_writable_to_false;

        $mock_is_dir_to_true = true;
        $mock_is_writable_to_false = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new File();
        $this->expectException(FileTransportException::class);
        $e->send($message);
    }

    public function testSendCannotWriteIntoFile(): void
    {
        global $mock_is_dir_to_true,
                $mock_is_writable_to_true,
                $mock_file_put_contents_to_false;

        $mock_is_dir_to_true = true;
        $mock_is_writable_to_true = true;
        $mock_file_put_contents_to_false = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new File();
        $this->expectException(FileTransportException::class);
        $e->send($message);
    }

    public function testSendSuccess(): void
    {
        global $mock_is_dir_to_true,
                $mock_is_writable_to_true,
                $mock_file_put_contents_to_true;

        $mock_is_dir_to_true = true;
        $mock_is_writable_to_true = true;
        $mock_file_put_contents_to_true = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new File();
        $this->assertTrue($e->send($message));
    }
}
