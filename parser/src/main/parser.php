<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 2:24
 */

require_once ('const.php');

class Q3DemoParser {

    private $file_name;

    /**
     * Q3DemoParser constructor.
     * @param string file_name - name of demo-file
     */
    public function __construct(string $file_name) {
        $this->file_name = $file_name;
    }

    /**
     *
     * @throws Exception
     * @return int messages count in this demo-file
     */
    public function countMessages () : int {
        $messageStream = new Q3MessageStream($this->file_name);
        try {
            while ($messageStream->nextMessage() != NULL) {
            }

            return $messageStream->getReadMessages();
        }
        finally{
            $messageStream->close();
        }
    }
}

class Q3DemoMessage {
    public $sequence;
    public $size;
    public $data;

    /**
     * Q3DemoMessage constructor.
     * @param $sequence
     * @param $size
     */
    public function __construct($sequence, $size)
    {
        $this->sequence = $sequence;
        $this->size = $size;
    }
}

class Q3MessageStream {
    private $fileHandle = FALSE;
    private $readBytes = 0;
    private $readMessages = 0;

    /**
     * Q3DemoParser constructor.
     * @param string file_name - name of demo-file
     * @throws Exception in case file is failed to open
     */
    public function __construct(string $file_name) {
        $this->readBytes = 0;
        $this->readMessages = 0;
        $this->fileHandle = fopen($file_name, "r");
        if ($this->fileHandle === FALSE)
            throw new Exception("can't open demofile ${file_name}...");
    }

    /**
     * @return Q3DemoMessage return a next message buffer or null if EOD is reached
     * @throws Exception in case stream is corrupted
     */
    public function nextMessage () {
        $header_buffer = fread($this->fileHandle, 8);
        if (!$header_buffer || strlen($header_buffer) != 8) {
            return null;
        }

        $this->readBytes += 8;
        $header = unpack("i*", $header_buffer);
//        var_dump($header);
        $sequence = $header[1];
        $msgLength =  $header[2];

        if ($sequence == -1 && $msgLength == -1) {
            // a normal case, end of message-sequence
            return null;
        }

        if ($msgLength < 0 || $msgLength > Q3_MESSAGE_MAX_SIZE) {
            throw new Exception("Demo file is corrupted, wrong message length: ${msgLength}");
        }

        $msg = new Q3DemoMessage ($sequence, $msgLength);
        $msg->data = fread($this->fileHandle, $msgLength);
        if (!$msg->data)
            throw new Exception("Unable to read demo-message, corrupted file?");

        $this->readBytes += $msgLength;
        $this->readMessages++;

        return $msg;
    }

    public function close () {
        if($this->fileHandle) {
            fclose($this->fileHandle);
            $this->fileHandle = FALSE;
        }
    }

    /**
     * @return int
     */
    public function getReadBytes(): int
    {
        return $this->readBytes;
    }

    /**
     * @return int
     */
    public function getReadMessages(): int
    {
        return $this->readMessages;
    }

    public function __destruct() {
        $this->close();
    }

}