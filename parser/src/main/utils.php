<?php

class BitStreamReader {

    // string, gets from IO calls (fread)
    private $data;

    private $bit_length;

    private $offsetIndex;
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
        $this->bit_length = strlen($data) * 8;
        $this->data = unpack("I*",$data.str_repeat("\0", 4-(($this->bit_length/8)&0x03)));
        $this->reset();
    }

    public function reset () {
        $this->bitIdx = 0;
        $this->offsetIndex = 1;
    }

    public function nextBit (): int {
        if ($this->bitIdx >= $this->bit_length)
            return -1;

        $bitPos = $this->bitIdx & 31;

        if ($bitPos == 0) {
            //unpack byte
            $this->offsetValue = $this->data[$this->offsetIndex];//ord($this->data[(int)($this->bitIdx / 8)]);
            $this->offsetIndex++;
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
