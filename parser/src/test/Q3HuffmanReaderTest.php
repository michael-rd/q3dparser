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
}
