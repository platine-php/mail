<?php

declare(strict_types=1);

namespace Platine\Test\Mail;

use Platine\Mail\Mailer;
use Platine\Mail\Message;
use Platine\Mail\Transport\NullTransport;
use Platine\PlatineTestCase;

/**
 * Mailer class tests
 *
 * @group core
 * @group mail
 * @group message
 */
class MailerTest extends PlatineTestCase
{

    public function testConstructorTransportIsNull()
    {
            $e = new Mailer();
            $this->assertInstanceOf(NullTransport::class, $e->getTransport());
    }

    public function testConstructorTransportIsNotNull()
    {
            $t = new NullTransport();
            $e = new Mailer($t);
            $this->assertInstanceOf(NullTransport::class, $e->getTransport());
            $this->assertEquals($t, $e->getTransport());
    }

    public function testSetGetTransport()
    {
        $e = new Mailer();
        $this->assertInstanceOf(NullTransport::class, $e->getTransport());

        $t = new NullTransport();
        $e->setTransport($t);
        $this->assertEquals($t, $e->getTransport());
    }

    public function testSend()
    {
        $message = $this->getMockInstance(Message::class);
        $e = new Mailer();
        $this->assertTrue($e->send($message));
    }
}
