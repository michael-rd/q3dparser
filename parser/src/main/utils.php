<?php

/**
 * Class BitStreamReader
 *
 * It helps to operate on byte-buffers.
 * All operations are organized to expose byte-buffer as stream of bits.
 * Constructor usually takes binary string after fread (file read) operation call.
 * There is no other logic except consequential reading of bits
 */
class BitStreamReader {

    // array of integers, first value have index '1'
    // this var holds result of unpack operation
    private $data;

    // the number of bits in this stream
    private $bit_length;

    // cached value of integer taken from $data
    private $currentBits;

    // index of bit (read position) in a virtual bit-stream
    // it'a a sequential number of reads from this stream
    private $bitIdx;


    /**
     * BitStreamReader constructor.
     * @param $data assumes it's a binary string taken from 'fread' call or array of integers
     */
    public function __construct($data)
    {
        if (is_string($data)) {
            $this->bit_length = strlen($data) * 8;
            // unpack binary string into array of integers
            //
            $this->data = unpack("I*",$data.str_repeat("\0", 4-(($this->bit_length/8)&0x03)));
        }
        else if (is_array($data)) {
            $this->bit_length = count($data) * 32;
            $this->data = $data;
        }

        $this->reset();
    }

    /**
     * Reset this stream. It sets read position to 0 (begin)
     */
    public function reset () {
        $this->bitIdx = 0;
//        $this->offsetIndex = 1;
        $this->currentBits = reset($this->data);
    }

    /**
     * Test if end-of-data is reached
     * @return bool return TRUE if end-of-data reached, else FALSE
     */
    public function isEOD () : bool {
        return $this->bitIdx >= $this->bit_length;
    }

    /**
     * Read required amount of bits ($bits) from this stream.
     * Result will have all bits in right-to-left order (a normal bits order),
     * so the first read bit will be lowest
     * @param int $bits amount of bits to read. value has to be in a range 1..32
     * @return int
     */
    public function readBits (int $bits) : int {
        if ($bits < 0 || $bits > 32 || $this->bitIdx + $bits > $this->bit_length)
            return -1;

        $value = 0;
        // bit mask to set for target value
        $setBit = 1;

        // cache read position, local variables access is much faster
        $intIdx = $this->bitIdx;
        // cache curr bits
        $intBits = $this->currentBits;


        // amount of bits we can read from current cached value
        $currAmount = 32 - ($intIdx & 31);
        $tread = $bits > $currAmount ? $currAmount : $bits;

//        echo "bits to read=${bits}, curr-amount = ${currAmount}, tread = ${tread}, bits-cache: ".decbin($intBits);

        $bits -= $tread;
        $intIdx += $tread;

        while ($tread > 0) {
            if ($intBits & 1)
                $value |= $setBit;

            $setBit <<= 1;
            $intBits >>= 1;
            --$tread;
        }

        if ($bits > 0) {
            // we have to switch to next int from data-buffer
            $intBits = next($this->data);
            $intIdx += $bits;

            while ($bits > 0) {
                if ($intBits & 1)
                    $value |= $setBit;

                $setBit <<= 1;
                $intBits >>= 1;
                --$bits;
            }
        }

        // write local values back
        $this->currentBits = $intBits;
        $this->bitIdx = $intIdx;
//        echo ", in end read-pos= ${intIdx} \n";

        return $value;
    }

    /**
     * Method read and return next bit value from this stream
     * @return int returns next bit value (0 or 1) or -1 in case end of data
     */
    public function nextBit (): int {
        if ($this->bitIdx >= $this->bit_length)
            return -1;

        $rez = $this->currentBits & 1;
        ++$this->bitIdx;

        if ($this->bitIdx & 31)
            $this->currentBits >>= 1;
        else
            $this->currentBits = next($this->data);

        return $rez;
    }

    /**
     * It skips amount of bits
     * @param int $skip value has to be in range 1..32
     * @return int returns current bit-read position of this stream
     */
    public function skipBits (int $skip) : int {
        if ($skip < 0 || $skip > 32 || $this->bitIdx + $skip > $this->bit_length)
            return -1;

        $currAmount = 32 - ($this->bitIdx & 31);
        $this->bitIdx += $skip;

        if ($currAmount > $skip) {
            $this->currentBits >>= $skip;
        }
        else {
            $this->currentBits = next($this->data);
            $skip -= $currAmount;
            $this->currentBits >>= $skip;
        }

        return $this->bitIdx;
    }
}


class Q3Utils {
    public static function ANGLE2SHORT (float $x) : int {
        return ((int)($x*65536.0/360.0)) & 65535;
    }

    public static function SHORT2ANGLE (int $x) : float {
        return ((float)$x*(360.0/65536.0));
    }

    public static function rawBitsToFloat (int $bits) : float {
        $sign = $bits & 0x80000000 ? -1 : 1;
        $e = ($bits >> 23) & 0xFF;
        $m = $e ? ($bits & 0x7fffff) | 0x800000 : ($bits & 0x7fffff) << 1;
        return $sign*$m*pow(2,$e-150);
    }

    public static function split_config($src) {
        $begin_ind = substr ( $src, 0, 1 ) == '\\' ? 1 : 0;
        $src = explode ( '\\', $src );
        $rez = array ();

        for($k = $begin_ind; $k < sizeof ( $src ); $k += 2) {
            $rez [strtolower ( $src [$k] )] = $src [$k + 1];
        }
        return $rez;
    }

}


/**
 * Helper class, simple profiler
 */
class Sprof {
    private $name;
    private $last_b;

    private $count = 0;
    private $total_time;

    public function __construct ($name) {
        $this->name = $name;
    }

    public function begin () {
        $this->last_b = microtime(true);
        $this->count++;
    }

    public function end () {
        $x = microtime(true);
        $this->total_time += ($x - $this->last_b);
    }

    public function getDebug () {
        return 'SP['.$this->name.'] : time='.$this->total_time.', count='.$this->count.', avg='.($this->count > 0 ? $this->total_time/$this->count : 0);
    }
}
