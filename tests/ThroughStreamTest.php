<?php

namespace React\Tests\Stream;

use React\Stream\ReadableStream;
use React\Stream\ThroughStream;

/**
 * @covers React\Stream\ThroughStream
 */
class ThroughStreamTest extends TestCase
{
    /** @test */
    public function itShouldEmitAnyDataWrittenToIt()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnceWith('foo'));
        $through->write('foo');
    }

    /** @test */
    public function pipingStuffIntoItShouldWork()
    {
        $readable = new ReadableStream();

        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnceWith('foo'));

        $readable->pipe($through);
        $readable->emit('data', array('foo'));
    }

    /** @test */
    public function endShouldEmitEndAndClose()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableNever());
        $through->on('end', $this->expectCallableOnce());
        $through->on('close', $this->expectCallableOnce());
        $through->end();
    }

    /** @test */
    public function endShouldCloseTheStream()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableNever());
        $through->end();

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function endShouldWriteDataBeforeClosing()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnceWith('foo'));
        $through->end('foo');

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function endTwiceShouldOnlyEmitOnce()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnce('first'));
        $through->end('first');
        $through->end('ignored');
    }

    /** @test */
    public function writeAfterEndShouldReturnFalse()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableNever());
        $through->end();

        $this->assertFalse($through->write('foo'));
    }

    /** @test */
    public function writeDataWillCloseStreamShouldReturnFalse()
    {
        $through = new ThroughStream();
        $through->on('data', array($through, 'close'));

        $this->assertFalse($through->write('foo'));
    }

    /** @test */
    public function itShouldBeReadableByDefault()
    {
        $through = new ThroughStream();
        $this->assertTrue($through->isReadable());
    }

    /** @test */
    public function itShouldBeWritableByDefault()
    {
        $through = new ThroughStream();
        $this->assertTrue($through->isWritable());
    }

    /** @test */
    public function pauseShouldDelegateToPipeSource()
    {
        $input = $this->getMockBuilder('React\Stream\ReadableStream')->setMethods(array('pause'))->getMock();
        $input
            ->expects($this->once())
            ->method('pause');

        $through = new ThroughStream();
        $input->pipe($through);

        $through->pause();
    }

    /** @test */
    public function resumeShouldDelegateToPipeSource()
    {
        $input = $this->getMockBuilder('React\Stream\ReadableStream')->setMethods(array('resume'))->getMock();
        $input
            ->expects($this->once())
            ->method('resume');

        $through = new ThroughStream();
        $input->pipe($through);

        $through->resume();
    }

    /** @test */
    public function closeShouldCloseOnce()
    {
        $through = new ThroughStream();

        $through->on('close', $this->expectCallableOnce());

        $through->close();

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function doubleCloseShouldCloseOnce()
    {
        $through = new ThroughStream();

        $through->on('close', $this->expectCallableOnce());

        $through->close();
        $through->close();

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function pipeShouldPipeCorrectly()
    {
        $output = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $output->expects($this->any())->method('isWritable')->willReturn(True);
        $output
            ->expects($this->once())
            ->method('write')
            ->with('foo');

        $through = new ThroughStream();
        $through->pipe($output);
        $through->write('foo');
    }
}
