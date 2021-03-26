<?php

declare(strict_types=1);

namespace Platine\Test\Mail;

use Platine\Mail\Message;
use Platine\PlatineTestCase;

/**
 * Message class tests
 *
 * @group core
 * @group mail
 * @group message
 */
class MessageTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $e = new Message();

        $this->assertTrue(true);
    }
}
