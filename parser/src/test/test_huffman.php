<?php
/**
 * Created by IntelliJ IDEA.
 * User: Mike
 * Date: 13.02.2018
 * Time: 5:41
 */
require_once '../main/huffman.php';

// source bytes
$src_bytes = array(0, 1, 2, 3, 4, 20, 255, 80, 0, 5);

// encoded hex string
$hex_str = '6E243BB43B92A5110000000000000000';

$decompressor = new Q3HuffmanReader (pack("H*", $hex_str));


foreach ($src_bytes as $k => $v) {

    $decoded = $decompressor->readByte();

    if ($v != $decoded) {
        throw new Exception("fail at ${k} => ${v}, in fact ${decoded}\n");
    }
    else {
        echo "ok, read ${decoded}\n";
    }
}