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
    public function testCountMessages () {
        $parser = new Q3DemoParser("demos/lucy-vchrkn[df.vq3]00.44.048(MichaelRD.Russia).dm_68");
        $this->assertEquals (6015, $parser->countMessages());
    }
}
