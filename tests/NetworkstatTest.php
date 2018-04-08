<?php

/**
 * PHP version 7.1
 *
 * @package AM\Networkstat
 */

namespace Tests;

use \PHPUnit\Framework\TestCase;
use \Logics\Tests\InternalWebServer;
use \AM\Networkstat\Networkstat;
use \Exception;

/**
 * Tests for networkstat
 *
 * @author Andrey Mashukov <a.mashukoff@gmail.com>
 *
 * @runTestsInSeparateProcesses
 */
class NetworkstatTest extends TestCase
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

        define("BITINFOCHARTS_URL", $this->host . "/bitinfocharts");
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
     * Should allow to get transactions hashrate and marketcap
     *
     * @return void
     */

    public function testShouldAllowToGetTransactionsHashrateAndMarketcap()
    {
        $netstat = new NetworkStat();
        $stat    = $netstat->get("BTC");

        $this->assertEquals(json_decode(file_get_contents(__DIR__ . "/testdata/expected.json"), true), $stat);
    } //end testShouldAllowToGetTransactionsHashrateAndMarketcap()


} //end class

?>
