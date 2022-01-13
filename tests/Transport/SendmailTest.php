<?php

declare(strict_types=1);

namespace Platine\Test\Sendmail\Transport;

use Platine\Mail\Exception\MailException;
use Platine\Mail\Message;
use Platine\Mail\Transport\Sendmail;
use Platine\Dev\PlatineTestCase;

/**
 * Sendmail class tests
 *
 * @group core
 * @group mail
 * @group transport
 */
class SendmailTest extends PlatineTestCase
{
    public function testSendPopenNotExists(): void
    {
        global $mock_function_exists_to_false;

        $mock_function_exists_to_false = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new Sendmail();
        $this->expectException(MailException::class);
        $e->send($message);
    }

    public function testSendPopenReturnFalse(): void
    {
        global $mock_function_exists_to_true,
                $mock_popen_to_false;

        $mock_function_exists_to_true = true;
        $mock_popen_to_false = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new Sendmail();
        $this->expectException(MailException::class);
        $e->send($message);
    }

    public function testSendPCloseReturnValueOtherThanZero(): void
    {
        global $mock_function_exists_to_true,
                $mock_pclose;

        $mock_function_exists_to_true = true;
        $mock_pclose = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new Sendmail();
        $this->expectException(MailException::class);
        $e->send($message);
    }

    public function testSendSuccess(): void
    {
        global $mock_function_exists_to_true,
                $mock_pclose_zero,
                $mock_popen_to_true,
                $mock_fputs;

        $mock_function_exists_to_true = true;
        $mock_pclose_zero = true;
        $mock_popen_to_true = true;
        $mock_fputs = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new Sendmail();
        $this->assertTrue($e->send($message));
    }
}
