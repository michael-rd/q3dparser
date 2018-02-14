<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 2/14/18
 * Time: 8:32 AM
 */

use PHPUnit\Framework\TestCase;
require '../main/utils.php';

class Q3UtilsTest extends TestCase
{

    public function testRawBitsToFloat()
    {
        $this->assertEquals(1.0, Q3Utils::rawBitsToFloat(0x3f800000));
        $this->assertEquals(0.15625, Q3Utils::rawBitsToFloat(0x3E200000));
        $this->assertEquals(0, Q3Utils::rawBitsToFloat(0));
        $this->assertEquals(-0.0, Q3Utils::rawBitsToFloat(0x80000000));
    }
}
