<?php

declare(strict_types=1);

namespace Platine\Test\Mail\Transport;

use Platine\Mail\Message;
use Platine\Mail\Transport\NullTransport;
use Platine\Dev\PlatineTestCase;

/**
 * NullTransport class tests
 *
 * @group core
 * @group mail
 * @group transport
 */
class NullTransportTest extends PlatineTestCase
{
    public function testSend(): void
    {
        $message = $this->getMockBuilder(Message::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $e = new NullTransport();

        $this->assertTrue($e->send($message));
    }
}
