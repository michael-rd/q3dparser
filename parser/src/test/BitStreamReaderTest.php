<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 14.02.2018
 * Time: 2:41
 */

use PHPUnit\Framework\TestCase;
require '../main/utils.php';

class BitStreamReaderTest extends TestCase
{
    public function testSingleBitRead () {
        $data = pack("H*", '77000000');
        foreach (unpack("I*", $data) as $d) {
            echo decbin($d).' ';
        }
        echo "\n";

        $test_bits = array(1,1,1,0,1,1,1,0,0,0,0,0);

        $stream = new BitStreamReader($data);

        foreach ($test_bits as $bit) {
            $this->assertEquals ($bit, $stream->nextBit());
        }
    }

    public function testReadSet () {
        $data = pack('H*', '77808080080000FF');

        foreach (unpack("I*", $data) as $d) {
            echo decbin($d).' ';
        }
        echo "\n";
        //  first int, low short in binary : 1 0111 0111
        //  lets read 3 bits at once, we expect it will be
        //  111b => 7, 110b => 6, 101b => 5
        $stream = new BitStreamReader($data);

        $this->assertEquals (7, $stream->readBits(3));
        $this->assertEquals (6, $stream->readBits(3));
        $this->assertEquals (1, $stream->readBits(3));

        // skip 22 bits, so we at highest bit in first int
        $stream->readBits(22);

        // expect 1000 1
        $this->assertEquals (17, $stream->readBits(5));

        $this->assertEquals (0xFF00000, $stream->readBits(28));


        $stream->reset();
        $this->assertEquals (31, $stream->skipBits(31));
        $this->assertEquals (17, $stream->readBits(5));
        $this->assertEquals (0xFF00000, $stream->readBits(28));

        $stream->reset();
        $this->assertEquals (5,  $stream->skipBits(5));
        $this->assertEquals (36, $stream->skipBits(31));
        $this->assertEquals (0xFF00000, $stream->readBits(28));

    }
}
