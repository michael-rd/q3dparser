<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 2:24
 */

require_once ('const.php');
require_once ('huffman.php');

class Q3DemoParser {

    private $file_name;

    /**
     * Q3DemoParser constructor.
     * @param string file_name - name of demo-file
     */
    public function __construct(string $file_name) {
        $this->file_name = $file_name;
    }


    public function parseConfig () {
        $msgParser = new Q3DemoConfigParser ();
        $this->doParse($msgParser);
        return $msgParser->hasConfigs() ? $msgParser->getRawConfigs() : NULL;
    }

    /**
     *
     * @throws Exception
     * @return int messages count in this demo-file
     */
    public function countMessages () : int {
        return $this->doParse (new Q3EmptyParser())->count;
    }

    private function doParse (AbstractDemoMessageParser $msgParser) : AbstractDemoMessageParser {
        $messageStream = new Q3MessageStream($this->file_name);
        try {
            $msg = NULL;
            while (($msg = $messageStream->nextMessage()) != NULL) {
                if (!$msgParser->parse($msg))
                    break;
            }
        }
        finally{
            $messageStream->close();
        }

        return $msgParser;
    }

    public static function getRawConfigStrings (string $file_name) {
        $p = new Q3DemoParser($file_name);
        return $p->parseConfig();
    }

    public static function getFriendlyConfig (string $file_name) {
        $conf = self::getRawConfigStrings($file_name);

        if (!isset($conf))
            return null;

        $result = array();

        if (isset($conf[Q3Const::Q3_DEMO_CFG_FIELD_CLIENT])) {
            $result['client'] = Q3Utils::split_config($conf[Q3Const::Q3_DEMO_CFG_FIELD_CLIENT]);
            $result['client_version'] = $result['client']['version'];
            $result['physic'] = $result['client']['df_promode'] == 0 ? 'vq3' : 'cpm';
        }

        if (isset($conf[Q3Const::Q3_DEMO_CFG_FIELD_GAME])) {
            $result['game'] = Q3Utils::split_config($conf[Q3Const::Q3_DEMO_CFG_FIELD_GAME]);
        }

        if (isset($conf[Q3Const::Q3_DEMO_CFG_FIELD_PLAYER])) {
            $result['player'] = Q3Utils::split_config($conf[Q3Const::Q3_DEMO_CFG_FIELD_PLAYER]);
        }

        $result['raw'] = $conf;

        return $result;
    }

    public static function countDemoMessages (string $file_name) : int {
        $p = new Q3DemoParser($file_name);
        return $p->countMessages();
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

interface AbstractDemoMessageParser {
    public function parse(Q3DemoMessage $message) : bool;
}


final class Q3EmptyParser implements AbstractDemoMessageParser {
    public $count = 0;

    public function parse(Q3DemoMessage $message) : bool {
        ++$this->count;
        return true;
    }
}

final class Q3DemoConfigParser implements AbstractDemoMessageParser {

    private $configs;

    public function hasConfigs () : bool {
        return isset($this->configs);
    }

    public function getRawConfigs () {
        return $this->configs;
    }

    public function parse(Q3DemoMessage $message) : bool {
        $reader = new Q3HuffmanReader ($message->data);

        //clc.reliableAcknowledge
        $reader->readLong();

        while (!$reader->isEOD()) {
            switch ($reader->readByte()) {
                case Q3_SVC::BAD:
                case Q3_SVC::NOP:
                    return false;

                case Q3_SVC::EOF:
                    return isset($this->configs);

                case Q3_SVC::SERVERCOMMAND:
                    $reader->readServerCommand();
                    break;

                case Q3_SVC::GAMESTATE:
                    $this->parseGameState($reader);
                    return isset($this->configs);

                case Q3_SVC::SNAPSHOT:
                    // snapshots couldn't be mixed with game-state command in a single message
                    return false;

                default:
                    // unknown command / corrupted stream
                    return false;
            }
        }
    }

    private function parseGameState (Q3HuffmanReader $reader) {
        //clc.serverCommandSequence
        $reader->readLong();

        while (true) {
            $cmd = $reader->readByte();
            if ($cmd == Q3_SVC::EOF)
                break;

            switch ($cmd) {
                case Q3_SVC::CONFIGSTRING:
                    $key = $reader->readShort();
                    if ($key < 0 || $key > Q3Const::MAX_CONFIGSTRINGS) {
                        //logger.debug("wrong config string key {}", key);
                        return;
                    }
                    if (!isset($this->configs))
                        $this->configs = array();

                    $this->configs[$key] = $reader->readBigString();
                    break;

                case Q3_SVC::BASELINE:
                    // assume Baseline command has to follow after config-strings
                    return;

                default:
                    //  bad command
                    return;
            }
        }

        //clc.clientNum
        $reader->readLong();

        //clc.checksumFeed
        $reader->readLong();
    }
}


