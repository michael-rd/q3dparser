<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 3:59
 */
require_once '../main/parser.php';

$parser = new Q3DemoParser("demos/lucy-vchrkn[df.vq3]00.44.048(MichaelRD.Russia).dm_68");
if ($parser->countMessages() != 6015)
    echo "fail";
else
    echo "success!";

