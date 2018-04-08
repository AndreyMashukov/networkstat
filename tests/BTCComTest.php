<?php

/**
 * PHP version 7.1
 *
 * @package AM\Networkstat
 */

namespace Tests;

use \PHPUnit\Framework\TestCase;
use \Logics\Tests\InternalWebServer;
use \AM\Networkstat\BTCCom;
use \Exception;

/**
 * BTC.com pools stat
 *
 * @author Andrey Mashukov <a.mashukoff@gmail.com>
 *
 * @runTestsInSeparateProcesses
 */
class BTCComTest extends TestCase
{

    use InternalWebServer;

    /**
     * Name folder which should be removed after tests
     *
     * @var string
     */
    protected $remotepath;

    /**
     * Testing host
     *
     * @var string
     */
    protected $host;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */

    protected function setUp()
    {
        $this->remotepath = $this->webserverURL();
        $this->host       = $this->remotepath . "/mock";

        define("BTC_COM_URL", $this->host . "/btc");
    } //end setUp()


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */

    protected function tearDown()
    {
        unset($this->remotepath);
    } //end tearDown()


    /**
     * Should allow to get pools data from btc.com
     *
     * @return void
     */

    public function testShouldAllowToGetPoolsDataFromBtcCom()
    {
        $expected = [
            "1D"  => json_decode(file_get_contents(__DIR__ . "/testdata/btc/1d.json"), true),
            "3D"  => json_decode(file_get_contents(__DIR__ . "/testdata/btc/3d.json"), true),
            "1W"  => json_decode(file_get_contents(__DIR__ . "/testdata/btc/1w.json"), true),
            "1M"  => json_decode(file_get_contents(__DIR__ . "/testdata/btc/1m.json"), true),
            "3M"  => json_decode(file_get_contents(__DIR__ . "/testdata/btc/3m.json"), true),
            "1Y"  => json_decode(file_get_contents(__DIR__ . "/testdata/btc/1y.json"), true),
            "ALL" => json_decode(file_get_contents(__DIR__ . "/testdata/btc/all.json"), true),
        ];

        $btc = new BTCCom();
        foreach ($expected as $key => $expected) {
            $this->assertEquals($expected, $btc->getPools($key));
        } //end foreach

    } //end testShouldAllowToGetPoolsDataFromBtcCom()


} //end class

?>
