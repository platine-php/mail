<?php

declare(strict_types=1);

namespace Platine\Test\Mail;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
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

    public function testConstructor()
    {
            $e = new Message();
            $this->assertInstanceOf(Message::class, $e);
    }

    public function testSetFrom()
    {
        global $mock_base64_encode, $mock_filter_var_to_false;

        $email = 'foo@bar.com';
        $name = null;

        $e = new Message();
        $this->assertEmpty($e->getFrom());

        $e->setFrom($email, $name);
        $this->assertEquals($email, $e->getFrom());

        $mock_base64_encode = true;

        $name = 'Bar';
        $e->setFrom($email, $name);
        $expected = sprintf('"=?UTF-8?B?%s?=" <%s>', $name, $email);
        $this->assertEquals($expected, $e->getFrom());

        //filter_var return false
        $mock_filter_var_to_false = true;
        $e->setFrom($email, $name);
        $expected = '"=?UTF-8?B??=" <>';
        $this->assertEquals($expected, $e->getFrom());
    }

    public function testSetTo()
    {
        global $mock_base64_encode;

        $mock_base64_encode = true;

        $email = 'foo@bar.com';
        $name = null;

        $e = new Message();
        $tos = $e->getTo();
        $this->assertEmpty($tos);

        $e->setTo($email, $name);
        $this->assertCount(1, $e->getTo());

        $name = 'Foo';
        $e->setTo($email, $name);
        $tos = $e->getTo();
        $this->assertCount(2, $tos);
        $this->assertEquals('foo@bar.com', $tos[0]);
        $expected = sprintf('"=?UTF-8?B?%s?=" <%s>', $name, $email);
        $this->assertEquals($expected, $tos[1]);

        $expected = sprintf(
            '%s, "=?UTF-8?B?%s?=" <%s>',
            'foo@bar.com',
            'Foo',
            'foo@bar.com'
        );
        $this->assertEquals($expected, $e->getEncodedTo());
    }

    public function testSetGetCc()
    {
        global $mock_base64_encode;

        $mock_base64_encode = true;

        $emails = array('foo@bar.com', 'baz' => 'baz@foo.com');

        $e = new Message();
        $this->assertEmpty($e->getCc());
        $e->setCc($emails);

        $cc = $e->getCc();
        $this->assertCount(2, $cc);
        $this->assertArrayHasKey(0, $cc);
        $this->assertArrayHasKey('baz', $cc);
        $this->assertEquals('foo@bar.com', $cc[0]);
        $this->assertEquals('baz@foo.com', $cc['baz']);
        $expected = sprintf(
            '%s, "=?UTF-8?B?%s?=" <%s>',
            'foo@bar.com',
            'baz',
            'baz@foo.com'
        );
        $this->assertEquals($expected, $e->getHeader('Cc'));
    }

    public function testSetGetBcc()
    {
        global $mock_base64_encode;

        $mock_base64_encode = true;

        $emails = array('foo' => 'foo@bar.com', 'baz' => 'baz@foo.com');

        $e = new Message();
        $this->assertEmpty($e->getBcc());
        $e->setBcc($emails);

        $bcc = $e->getBcc();
        $this->assertCount(2, $bcc);
        $this->assertArrayHasKey('foo', $bcc);
        $this->assertArrayHasKey('baz', $bcc);
        $this->assertEquals('foo@bar.com', $bcc['foo']);
        $this->assertEquals('baz@foo.com', $bcc['baz']);
        $expected = sprintf(
            '"=?UTF-8?B?%s?=" <%s>, "=?UTF-8?B?%s?=" <%s>',
            'foo',
            'foo@bar.com',
            'baz',
            'baz@foo.com'
        );
        $this->assertEquals($expected, $e->getHeader('Bcc'));

        //Param is empty
        $this->expectException(InvalidArgumentException::class);
        $e->setBcc([]);
    }

    public function testSetReplyTo()
    {
        global $mock_base64_encode;

        $mock_base64_encode = true;

        $email = 'foo@bar.com';
        $name = null;

        $e = new Message();
        $this->assertEmpty($e->getHeader('Reply-To'));

        $e->setReplyTo($email, $name);
        $this->assertEquals($email, $e->getHeader('Reply-To'));

        $name = 'Bar';
        $e->setReplyTo($email, $name);
        $expected = sprintf('"=?UTF-8?B?%s?=" <%s>', $name, $email);
        $this->assertEquals($expected, $e->getHeader('Reply-To'));
    }

    public function testSetHtml()
    {
        $e = new Message();
        $headers = $this->getPropertyValue(Message::class, $e, 'headers');
        $this->assertEmpty($headers);

        $e->setHtml();
        $expected = 'text/html; charset="UTF-8"';
        $this->assertEquals($expected, $e->getHeader('Content-Type'));
    }

    public function testToString()
    {
        global $mock_date, $mock_base64_encode, $mock_chunk_split;

        $mock_date = true;
        $mock_base64_encode = true;
        $mock_chunk_split = true;

        $file = $this->getFileAttachment();

        $e = new Message();

        $uid = $this->getPropertyValue(Message::class, $e, 'uid');

        $e->setFrom('from@email.com')
            ->setTo('to@email.com')
            ->setSubject('Subject')
            ->addAttachment($file->url())
            ->setBody('Body');
        $content = (string) $e;
        $expected = 'From: from@email.com
Return-Path: from@email.com
Reply-To: from@email.com
X-Priority: 3
X-Mailer: Platine PHP Mail
Subject: =?UTF-8?B?Subject?=
To: to@email.com
Date: 2021-01-01
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="' . $uid . '"

This is a multi-part message in MIME format.
--' . $uid . '
Content-Type: text/html; charset="UTF-8"
Content-Transfer-Encoding: base64


Body


--' . $uid . '
Content-Type: application/octet-stream; name="=?UTF-8?B?attachment.pdf?="
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="=?UTF-8?B?attachment.pdf?="

foo bar

--' . $uid . '--';
        $this->assertEquals($expected, $content);
    }

    public function testSubject()
    {
        global $mock_base64_encode;

        $mock_base64_encode = true;

        $e = new Message();
        $subject = 'foo bar';
        $e->setSubject($subject);
        $expected = sprintf('=?UTF-8?B?%s?==?UTF-8?B? ?==?UTF-8?B?%s?=', 'foo', 'bar');
        $this->assertEquals($expected, $e->getSubject());
    }

    public function testSetBody()
    {
        $e = new Message();
        $body = $this->getPropertyValue(Message::class, $e, 'body');
        $this->assertEmpty($body);

        $content = 'foo message';
        $e->setBody($content);
        $body = $this->getPropertyValue(Message::class, $e, 'body');
        $this->assertEquals($content, $body);
    }

    public function testAddAttachmentDataIsNull()
    {
        global $mock_filesize_to_false, $mock_fread_to_false;

        $mock_filesize_to_false = true;
        $mock_fread_to_false = true;

        $e = new Message();
        $this->assertFalse($e->hasAttachments());

        $file = $this->getFileAttachment();

        $e->addAttachment($file->url());
        $this->assertFalse($e->hasAttachments());

        $mock_filesize_to_false = false;
        $e->addAttachment($file->url());
        $this->assertFalse($e->hasAttachments());
    }

    public function testAddAttachmentFilenameIsEmptyOrSet()
    {
        $this->addAttachmentFilenamesTests(null);
        $this->addAttachmentFilenamesTests('my_filename');
    }

    public function testAddAttachmentFileNotExist()
    {
        $e = new Message();
        $this->assertFalse($e->hasAttachments());

        $this->expectException(InvalidArgumentException::class);
        $e->addAttachment('xxx_not_existz_attachment.ext');
    }


    public function testWrap()
    {
        $e = new Message();
        $wrap = $this->getPropertyValue(Message::class, $e, 'wrap');

        //default value is 70
        $this->assertSame(70, $wrap);

        //negative value
        $e->setWrap(-1);
        $this->assertSame(70, $wrap);

        $e->setWrap(100);
        $wrap = $this->getPropertyValue(Message::class, $e, 'wrap');
        $this->assertSame(100, $wrap);
    }

    public function testPriority()
    {
        $e = new Message();
        $p = $this->getPropertyValue(Message::class, $e, 'priority');

        //default value is 3
        $this->assertSame(3, $p);

        //outside range
        $e->setPriority(6);
        $this->assertSame(3, $p);

        $e->setPriority(0);
        $this->assertSame(3, $p);

        $e->setPriority(4);
        $p = $this->getPropertyValue(Message::class, $e, 'priority');
        $this->assertSame(4, $p);
    }

    private function addAttachmentFilenamesTests(?string $filename)
    {
        global $mock_base64_encode, $mock_chunk_split;

        $mock_base64_encode = true;
        $mock_chunk_split = true;

        $e = new Message();
        $this->assertFalse($e->hasAttachments());

        $file = $this->getFileAttachment();

        $e->addAttachment($file->url(), $filename);
        $this->assertTrue($e->hasAttachments());
        $attachments = $this->getPropertyValue(Message::class, $e, 'attachments');
        $this->assertCount(1, $attachments);
        $this->assertIsArray($attachments[0]);
        $this->assertArrayHasKey('file', $attachments[0]);
        $this->assertArrayHasKey('data', $attachments[0]);
        $this->assertArrayHasKey('path', $attachments[0]);
        $this->assertEquals($file->url(), $attachments[0]['path']);
        $expectedFilename = sprintf('=?UTF-8?B?%s?=', $filename === null ? $file->getName() : $filename);
        $this->assertEquals($expectedFilename, $attachments[0]['file']);
        $this->assertEquals('foo bar', $attachments[0]['data']);
    }

    private function getFileAttachment(): vfsStreamFile
    {
        $vfsRoot = vfsStream::setup();
        $vfsFilesPath = vfsStream::newDirectory('test_files')->at($vfsRoot);

        $file = $this->createVfsFile('attachment.pdf', $vfsFilesPath, 'foo bar');

        return $file;
    }
}
