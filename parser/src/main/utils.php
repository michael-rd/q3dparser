<?php

class BitStreamReader {

    // string, gets from IO calls (fread)
    private $data;

    // length in bytes
    private $data_length;

    private $bit_length;

    // byte value of $data[$offsetIndex]
    private $offsetValue;

    // index of bit in a virtual bit-stream, it'a a sequential number of reads from this stream
    private $bitIdx;


    /**
     * BitStreamReader constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->data_length = strlen($data);
        $this->bit_length = $this->data_length * 8;
        $this->bitIdx = 0;
    }

    public function reset () {
        $this->bitIdx = 0;
    }

    public function nextBit (): int {
        if ($this->bitIdx >= $this->bit_length)
            return -1;

        $bitPos = $this->bitIdx & 7;

        if ($bitPos == 0) {
            //unpack byte
            $this->offsetValue = $this->data[(int)($this->bitIdx / 8)];
        }

        $this->bitIdx++;
        $rez = $this->offsetValue & 1;
        $this->offsetValue >>= 1;
        return $rez;
    }


//    private static $BYTE_BITS = array (0x80,0x40,0x20,0x10,0x08,0x04,0x02,0x01);
//    private static $BYTE_BITS_REV = array (0x01,0x02,0x04,0x08,0x10,0x20,0x40,0x80);
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
