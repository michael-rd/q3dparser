<?php
require_once("../main/utils.php");

$data = str_repeat ('0123456789', 1000);

$data_len = strlen($data);

echo "using buffer of ${data_len} bytes\n";

$reader = new BitStreamReader ($data);

$prof_timer = new Sprof ("BSI");

$count = 0;
for ($i = 0; $i < 500; $i++) {

    $prof_timer->begin();

    while ($reader->nextBit() >= 0) {
        $count++;
    }

    $prof_timer->end();
    $reader->reset();
}

echo $prof_timer->getDebug()."\n";
echo "total bits: ${count}\n";