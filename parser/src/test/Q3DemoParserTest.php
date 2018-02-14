<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 15:15
 */

use PHPUnit\Framework\TestCase;

require_once '../main/parser.php';


class Q3DemoParserTest extends TestCase {
    public function testCountMessages001 () {
        $parser = new Q3DemoParser("demos/lucy-vchrkn[df.vq3]00.44.048(MichaelRD.Russia).dm_68");
        $this->assertEquals (6015, $parser->countMessages());
    }


    public function testCountMessages002 () {
        $this->assertEquals (6015, Q3DemoParser::countDemoMessages("demos/lucy-vchrkn[df.vq3]00.44.048(MichaelRD.Russia).dm_68"));
    }

    public function testParseConfigStrings001 () {
        $raw_cfg = Q3DemoParser::getRawConfigStrings("demos/lucy-vchrkn[df.vq3]00.44.048(MichaelRD.Russia).dm_68");
        $this->assertNotNull ($raw_cfg);

        var_dump($raw_cfg);
    }

    public function testParseConfigStrings002 () {
        $cfg = Q3DemoParser::getFriendlyConfig("demos/lucy-vchrkn[df.vq3]00.44.048(MichaelRD.Russia).dm_68");
        $this->assertNotNull ($cfg);

        print_r($cfg);
    }
}
