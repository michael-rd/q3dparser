<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 4:42
 */

define('Q3_HUFFMAN_NYT_SYM', 0xFFFFFFFF);
require_once 'utils.php';

class Q3HuffmanReader {

    private $stream;

    /**
     * HFReader constructor.
     */
    public function __construct($buffer)
    {
        $this->stream = new BitStreamReader($buffer);
    }

    public function isEOD () : bool {
        return $this->stream->isEOD();
    }

    public function readNumBits (int $bits) {
        $value = 0;
        $neg = $bits < 0;

            if ($neg)
                $bits = $bits*-1;

            $fragmentBits = $bits & 7;

            if ($fragmentBits != 0) {
                $value = $this->stream.readBits($fragmentBits);
                $bits -= $fragmentBits;
            }

            if ($bits > 0) {
                $decoded = 0;
                for ($i = 0; $i < $bits; $i+=8) {
                    $sym = Q3HuffmanMapper::decodeSymbol($this->stream);
                    if ($sym == Q3_HUFFMAN_NYT_SYM)
                        return -1;

                    $decoded |= ($sym << $i);
                }

                if ($fragmentBits > 0)
                    $decoded <<= $fragmentBits;

                $value |= $decoded;
            }

            if ($neg) {
                if ( ($value & ( 1 << ( $bits - 1 ))) != 0 ) {
                    $value |= -1 ^ ( ( 1 << $bits ) - 1 );
                }
            }

            return $value;
    }

    public function readNumber ($bits) : int {
        return $bits == 8 ? Q3HuffmanMapper::decodeSymbol($this->stream) : $this->readBits($bits);
    }

    public function readByte () : int {
        return Q3HuffmanMapper::decodeSymbol($this->stream);
    }

    public function readShort () : int {
        return $this->readBits(16);
    }

    public function readInt () : int {
        return $this->readBits(32);
    }

    public function readLong () : int {
        return $this->readBits(32);
    }

//    public function readFloat () : float {
//        $ival = $this->readBits(32);
//        return Float.intBitsToFloat(ival);
//    }

    public function readAngle16 () : float {
        return Q3Utils::SHORT2ANGLE(readShort());
    }
}



class Q3HuffmanMapper {

    private static $rootNode;

    public static function decodeSymbol (BitStreamReader $reader) : int {
        $node = self::$rootNode;

        while ($node != null && $node->symbol == Q3_HUFFMAN_NYT_SYM) {
            $bit = $reader->nextBit();
            if ($bit < 0)
                return null;

            $node = $bit == 0 ? $node->left : $node->right;
        }

        return $node == null ? Q3_HUFFMAN_NYT_SYM : $node->symbol;
    }

    static function __init () {
        /*
         * this is packed map of q3-huffman tree
         * array contains bits sequences in reverse order, prefixed by bit. each value is the bit-coded path in tree,
         * while index of this array is the decoded value
         *
         * for example, the first one (having index 0) is '0x6' in hex and '110' in binary,
         * read them right-to-left : 0 (left node), 1 (right-node) => 0 (decoded value)
         *
         * second example: value  0x00A5 at index 16, 0xA5 = 10100101b, read bits in right-to-left order:
         * 1 (right), 0 (left), 1 (right), 0 (left), 0 (left), 1 (right), 0 (left) => 16 (decoded value)
         */
        $symtab = array(
            0x0006, 0x003B, 0x00C8, 0x00EC, 0x01A1, 0x0111, 0x0090, 0x007F, 0x0035, 0x00B4, 0x00E9, 0x008B, 0x0093, 0x006D, 0x0139, 0x02AC,
            0x00A5, 0x0258, 0x03F0, 0x03F8, 0x05DD, 0x07F3, 0x062B, 0x0723, 0x02F4, 0x058D, 0x04AB, 0x0763, 0x05EB, 0x0143, 0x024F, 0x01D4,
            0x0077, 0x04D3, 0x0244, 0x06CD, 0x07C5, 0x07F9, 0x070D, 0x07CD, 0x0294, 0x05AC, 0x0433, 0x0414, 0x0671, 0x06F0, 0x03F4, 0x0178,
            0x00A7, 0x01C3, 0x01EF, 0x0397, 0x0153, 0x01B1, 0x020D, 0x0361, 0x0207, 0x02F1, 0x0399, 0x0591, 0x0523, 0x02BC, 0x0344, 0x05F3,
            0x01CF, 0x00D0, 0x00FC, 0x0084, 0x0121, 0x0151, 0x0280, 0x0270, 0x033D, 0x0463, 0x06D7, 0x0771, 0x039D, 0x06AB, 0x05C7, 0x0733,
            0x032C, 0x049D, 0x056B, 0x076B, 0x05D3, 0x0571, 0x05E3, 0x0633, 0x04D7, 0x06CB, 0x0370, 0x02A8, 0x02C7, 0x0305, 0x02EB, 0x01D8,
            0x02F3, 0x013C, 0x03AB, 0x038F, 0x0297, 0x00B0, 0x0141, 0x034F, 0x005C, 0x0128, 0x02BD, 0x02C4, 0x0198, 0x028F, 0x010C, 0x01B3,
            0x0185, 0x018C, 0x0147, 0x0179, 0x00D9, 0x00C0, 0x0117, 0x0119, 0x014B, 0x01E1, 0x01A3, 0x0173, 0x016F, 0x00E8, 0x0088, 0x00E5,
            0x005F, 0x00A9, 0x00CC, 0x00FD, 0x010F, 0x0183, 0x0101, 0x0187, 0x0167, 0x01E7, 0x0157, 0x0174, 0x03CB, 0x03C4, 0x0281, 0x024D,
            0x0331, 0x0563, 0x0380, 0x07D7, 0x042B, 0x0545, 0x046B, 0x043D, 0x072B, 0x04F9, 0x04E3, 0x0645, 0x052B, 0x0431, 0x07EB, 0x05B9,
            0x0314, 0x05F9, 0x0533, 0x042C, 0x06DD, 0x05C1, 0x071D, 0x05D1, 0x0338, 0x0461, 0x06E3, 0x0745, 0x066B, 0x04CD, 0x04CB, 0x054D,
            0x0238, 0x07C1, 0x063D, 0x07BC, 0x04C5, 0x07AC, 0x07E3, 0x0699, 0x07D3, 0x0614, 0x0603, 0x05BC, 0x069D, 0x0781, 0x0663, 0x048D,
            0x0154, 0x0303, 0x015D, 0x0060, 0x0089, 0x07C7, 0x0707, 0x01B8, 0x03F1, 0x062C, 0x0445, 0x0403, 0x051D, 0x05C5, 0x074D, 0x041D,
            0x0200, 0x07B9, 0x04DD, 0x0581, 0x050D, 0x04B9, 0x05CD, 0x0794, 0x05BD, 0x0594, 0x078D, 0x0558, 0x07BD, 0x04C1, 0x07DD, 0x04F8,
            0x02D1, 0x0291, 0x0499, 0x06F8, 0x0423, 0x0471, 0x06D3, 0x0791, 0x00C9, 0x0631, 0x0507, 0x0661, 0x0623, 0x0118, 0x0605, 0x06C1,
            0x05D7, 0x04F0, 0x06C5, 0x0700, 0x07D1, 0x07A8, 0x061D, 0x0D00, 0x0405, 0x0758, 0x06F9, 0x05A8, 0x06B9, 0x068D, 0x00AF, 0x0064
        );

        self::$rootNode = new Q3HuffmanNode();
        // build huffman tree
        foreach ($symtab as $sym => $path)
            self::_put_sym($sym, $path);
    }

    private static function _put_sym ($sym, $path) {
        $node = self::$rootNode;

        while ($path > 1) {
            if ($path & 0x1) {
                // right side
                if ($node->right == NULL) {
                    $node->right = new Q3HuffmanNode();
                }

                $node = $node->right;
            }
            else {
                // left side
                if ($node->left == NULL) {
                    $node->left = new Q3HuffmanNode();
                }

                $node = $node->left;
            }
            $path >>= 1;
        }
        $node->symbol = $sym;
    }
}

class Q3HuffmanNode
{
    public $left;
    public $right;
    public $symbol;

    /**
     * Q3HuffmanNode constructor.
     * @param $symbol
     */
    public function __construct()
    {
        $this->symbol = Q3_HUFFMAN_NYT_SYM;
    }
}

Q3HuffmanMapper::__init ();