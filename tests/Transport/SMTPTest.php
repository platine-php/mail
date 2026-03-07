<?php

declare(strict_types=1);

namespace Platine\Test\SMTP\Transport;

use Platine\Dev\PlatineTestCase;
use Platine\Mail\Exception\SMTPException;
use Platine\Mail\Exception\SMTPRetunCodeException;
use Platine\Mail\Exception\SMTPSecureException;
use Platine\Mail\Message;
use Platine\Mail\Transport\SMTP;

/**
 * SMTP class tests
 *
 * @group core
 * @group mail
 * @group transport
 */
class SMTPTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $host = 'x.x.x.x';
        $port = 26;
        $timeout = 100;
        $rtimeout = 100;
        $e = new SMTP(
            $host,
            $port,
            $timeout,
            $rtimeout
        );

        $this->assertEquals($host, $this->getPropertyValue(SMTP::class, $e, 'host'));
        $this->assertEquals($port, $this->getPropertyValue(SMTP::class, $e, 'port'));
        $this->assertEquals($timeout, $this->getPropertyValue(SMTP::class, $e, 'timeout'));
        $this->assertEquals($rtimeout, $this->getPropertyValue(SMTP::class, $e, 'responseTimeout'));
    }

    public function testTimeOuts(): void
    {
        $host = 'x.x.x.x';
        $e = new SMTP($host);

        $this->assertEquals(30, $this->getPropertyValue(SMTP::class, $e, 'timeout'));
        $this->assertEquals(10, $this->getPropertyValue(SMTP::class, $e, 'responseTimeout'));

        $e->setTimeout(35);
        $e->setResponseTimeout(100);

        $this->assertEquals(35, $this->getPropertyValue(SMTP::class, $e, 'timeout'));
        $this->assertEquals(100, $this->getPropertyValue(SMTP::class, $e, 'responseTimeout'));
    }

    public function testSetEncryption(): void
    {
        $host = 'x.x.x.x';
        $e = new SMTP($host);

        $this->assertEquals(
            SMTP::ENCRYPTION_NONE,
            $this->getPropertyValue(SMTP::class, $e, 'encryption')
        );

        $e->setEncryption(SMTP::ENCRYPTION_TLS);

        $this->assertEquals(
            SMTP::ENCRYPTION_TLS,
            $this->getPropertyValue(SMTP::class, $e, 'encryption')
        );

        $this->expectException(SMTPException::class);
        $e->setEncryption('fake');
    }

    public function testAuth(): void
    {
        $host = 'x.x.x.x';
        $e = new SMTP($host);

        $username = 'foo';
        $password = 'bar';
        $e->setAuth($username, $password);

        $this->assertEquals($username, $this->getPropertyValue(SMTP::class, $e, 'username'));
        $this->assertEquals($password, $this->getPropertyValue(SMTP::class, $e, 'password'));
    }

    public function testSendCannotConnect(): void
    {
        global $mock_is_resource_to_false,
               $mock_fsockopen_to_true;

        $mock_is_resource_to_false = true;
        $mock_fsockopen_to_true = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $this->expectException(SMTPException::class);
        $e->send($message);
    }

    public function testSendNoValidResponse(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout;

        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = ['NoValidResponse'];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $this->expectException(SMTPException::class);
        $e->send($message);
    }

    public function testSendCannotConnectSslAndWrongReturnCode(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout;

        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = ['345 NotOK'];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendEhloStepError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = ['220 OK', '300 NotOK'];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendStartTlsStepError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = ['220 OK', '250 OK', '250 NotOK'];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setEncryption(SMTP::ENCRYPTION_STARTTLS);
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendStartTlsSetCryptoError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs,
                $mock_stream_socket_enable_crypto_to_false;

        $mock_stream_socket_enable_crypto_to_false = true;
        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = ['220 OK', '250 OK', '220 OK'];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setEncryption(SMTP::ENCRYPTION_STARTTLS);
        $this->expectException(SMTPSecureException::class);
        $e->send($message);
    }

    public function testSendAuthLoginEmpty(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs,
                $mock_stream_socket_enable_crypto_to_true;

        $mock_stream_socket_enable_crypto_to_true = true;
        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = ['220 OK', '250 OK', '220 OK', '250 OK', '210 NotOK'];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendAuthLoginServerDoesNotSupport(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = ['220 OK', '250 OK', '220 NotOK'];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendAuthLoginWrongLogin(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '300 NotOK',
        ];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendAuthLoginWrongPassword(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '300 NotOK',
        ];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendMailFromStepError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '235 OK',
            '300 NotOK',
        ];

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendRecipientStepError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '235 OK',
            '250 OK',
            '300 NotOK',
        ];

        $message = $this->getMockInstance(Message::class, [
            'getTo' => ['foo@bar.com'],
            'getCc' => [],
            'getBcc' => []
        ]);

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendDataCommandStepError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '235 OK',
            '250 OK',
            '250 OK',
            '300 NotOK',
        ];

        $message = $this->getMockInstance(Message::class, [
            'getTo' => ['foo@bar.com'],
            'getCc' => [],
            'getBcc' => []
        ]);

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendDatasStepError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '235 OK',
            '250 OK',
            '250 OK',
            '354 OK',
            '300 NotOK',
        ];

        $message = $this->getMockInstance(Message::class, [
            'getTo' => ['foo@bar.com'],
            'getCc' => [],
            'getBcc' => []
        ]);

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendQuitStepError(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs;

        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '235 OK',
            '250 OK',
            '250 OK',
            '354 OK',
            '250 OK',
            '300 NotOK',
        ];

        $message = $this->getMockInstance(Message::class, [
            'getTo' => ['foo@bar.com'],
            'getCc' => [],
            'getBcc' => []
        ]);

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->expectException(SMTPRetunCodeException::class);
        $e->send($message);
    }

    public function testSendSuccess(): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs,
                $mock_fclose_to_true;

        $mock_fclose_to_true = true;
        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '235 OK',
            '250 OK',
            '250 OK',
            '354 OK',
            '250 OK',
            '221 OK',
        ];

        $message = $this->getMockInstance(Message::class, [
            'getTo' => ['foo@bar.com'],
            'getCc' => [],
            'getBcc' => []
        ]);

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->assertTrue($e->send($message));
    }

    public function testSendFailed(): void
    {
        global $mock_is_resource_array,
               $mock_is_resource_array_content,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
               $mock_fgets_return_content,
               $mock_stream_set_timeout,
               $mock_fputs,
               $mock_fclose_to_true;

        $mock_fclose_to_true = true;
        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_array = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_fgets_return_content = [
            '220 OK',
            '250 OK',
            '334 OK',
            '334 OK',
            '235 OK',
            '250 OK',
            '250 OK',
            '354 OK',
            '250 OK',
            '221 OK',
        ];
        $mock_is_resource_array_content = [
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            false
        ];

        $message = $this->getMockInstance(Message::class, [
            'getTo' => ['foo@bar.com'],
            'getCc' => [],
            'getBcc' => []
        ]);

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $this->assertFalse($e->send($message));
        $this->assertCount(9, $e->getCommands());
        $this->assertCount(10, $e->getResponses());
    }


    public function testSendTLS(): void
    {
        $this->sendSuccessWithEncryption(SMTP::ENCRYPTION_TLS);
    }

    public function testSendSTARTTLS(): void
    {
        $this->sendSuccessWithEncryption(SMTP::ENCRYPTION_STARTTLS);
    }

    protected function sendSuccessWithEncryption(string $encrytion): void
    {
        global $mock_is_resource_to_true,
               $mock_fsockopen_to_true,
               $mock_fgets_to_string,
                $mock_fgets_return_content,
                $mock_stream_set_timeout,
                $mock_fputs,
                $mock_fclose_to_true,
                $mock_stream_socket_enable_crypto_to_true;

        $mock_fclose_to_true = true;
        $mock_fputs = true;
        $mock_stream_set_timeout = true;
        $mock_is_resource_to_true = true;
        $mock_fsockopen_to_true = true;
        $mock_fgets_to_string = true;
        $mock_stream_socket_enable_crypto_to_true = true;
        if ($encrytion === SMTP::ENCRYPTION_TLS) {
            $mock_fgets_return_content = [
                '220 OK',
                '250 OK',
                '334 OK',
                '334 OK',
                '235 OK',
                '250 OK',
                '250 OK',
                '354 OK',
                '250 OK',
                '221 OK',
            ];
        } else {
            $mock_fgets_return_content = [
                '220 OK',
                '250 OK',
                '220 OK',
                '250 OK',
                '334 OK',
                '334 OK',
                '235 OK',
                '250 OK',
                '250 OK',
                '354 OK',
                '250 OK',
                '221 OK',
            ];
        }

        $message = $this->getMockInstance(Message::class, [
            'getTo' => ['foo@bar.com'],
            'getCc' => [],
            'getBcc' => []
        ]);

        $host = 'x.x.x.x';
        $e = new SMTP($host);
        $e->setAuth('foo', 'bar');
        $e->setEncryption($encrytion);
        $this->assertTrue($e->send($message));
    }
}
