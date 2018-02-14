<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 15:08
 */

use PHPUnit\Framework\TestCase;
require_once '../main/huffman.php';



class Q3HuffmanReaderTest extends TestCase
{
    public function testDecode001 () {
        // source bytes
        $src_bytes = array(0, 1, 2, 3, 4, 20, 255, 80, 0, 5);
        // encoded data, hex string (compare with result taken from original q3-client)
        $hex_str = '6E243BB43B92A5110000000000000000';

        //
        $decompressor = new Q3HuffmanReader (pack("H*", $hex_str));

        foreach ($src_bytes as $k => $v) {
            $this->assertEquals ($v, $decompressor->readByte());
        }
    }

    public function testDecode002 () {
        $hex_str = '6E243BB43B92A5110000000000000000';

        $decompressor = new Q3HuffmanReader (pack("H*", $hex_str));

        $this->assertEquals (0x03020100, $decompressor->readInt());
        $this->assertEquals (0x50FF1404, $decompressor->readInt());
        $this->assertEquals (0x0500, $decompressor->readShort());
    }

    public function testDecode003 () {
        // this is a 'Hello World!' encoded in huffman bit-stream
        $hex_str = '3D619898B3F78CB3479897A611000000';

        $decompressor = new Q3HuffmanReader (pack("H*", $hex_str));

        $this->assertEquals ('Hello World!', $decompressor->readString());
    }
}
