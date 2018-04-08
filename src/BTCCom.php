<?php

/**
 * PHP version 7.1
 *
 * @package AM\Networkstat
 */

namespace AM\Networkstat;

use \DOMDocument;
use \DOMXPath;
use \Logics\Foundation\HTTP\HTTPclient;

/**
 * BTC.com pool statistics reader
 *
 * @author Andrey Mashukov <a.mashukoff@gmail.com>
 */
class BTCCom
{

    /**
     * Get pools
     *
     * @param string $interval Pools data interval
     * @param string $history  History year
     *
     * @return array Pools data
     */

    public function getPools(string $interval = "1D", string $history = "latest"): array
    {
        $mode = [
            "1D"  => "day",
            "3D"  => "day3",
            "1W"  => "week",
            "1M"  => "month",
            "3M"  => "month3",
            "1Y"  => "year",
            "ALL" => "all",
        ];

        $data = [];

        if (preg_match("/(latest|20[0-9]{2})/ui", $history) > 0) {
            $http = new HTTPclient(BTC_COM_URL . "/stats/pool?percent_mode=" . $history . "&pool_mode=" . $mode[$interval] . "#pool-history");
            $html = $http->get();
            $data = $this->_parsePoolsData($this->_findPoolsData($html));
        } //end if

        return $data;
    } //end getPools()


    /**
     * Find pools data block
     *
     * @param string $html HTML for search
     *
     * @return string HTML block
     */

    private function _findPoolsData(string $html): string
    {
        $dom = new DOMDocument("1.0", "utf-8");
        @$dom->loadHTML(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
        $xpath = new DOMXPath($dom);
        $list  = $xpath->query("//script[contains(., 'var globals')]");

        if ($list->length > 0) {
            return $dom->saveHTML($list[0]);
        }

        return "";
    } //end _findPoolsData()


    /**
     * Parse pools JS script data
     *
     * @param string $html <script> tag with JS code
     *
     * @return array Parsed data
     */

    private function _parsePoolsData(string $html): array
    {
        $data     = [];
        $html     = preg_replace("/(varglobals=|;)/ui", "",
            preg_replace("/(\n|\t|\b|\s+|)/ui", "", trim(strip_tags($html))) . "]");
        $patterns = [
            "pools"   => "/\{chartWidth:330,chartHeight:240,pools:(?P<data>.*),poolHistoryData:.*/ui",
            "history" => "/.*poolPercentHistory:(?P<data>.*),start:.*/ui",
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $html, $result) > 0) {
                $data[$key] = json_decode($result["data"], true);
            } //end if

        } //end foreach

        return $data;
    } //end _parsePoolsData()


} //end class

?>

