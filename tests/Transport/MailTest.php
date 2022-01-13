<?php

declare(strict_types=1);

namespace Platine\Test\Mail\Transport;

use Platine\Mail\Message;
use Platine\Mail\Transport\Mail;
use Platine\Dev\PlatineTestCase;

/**
 * Mail class tests
 *
 * @group core
 * @group mail
 * @group transport
 */
class MailTest extends PlatineTestCase
{
    public function testSend(): void
    {
        global $mock_mail;

        $mock_mail = true;

        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new Mail();

        $this->assertTrue($e->send($message));
    }
}
