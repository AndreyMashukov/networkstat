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
 * Network statistics class
 *
 * @author Andrey Mashukov <a.mashukoff@gmail.com>
 */
class Networkstat
{

    /**
     * Get networkstats
     *
     * @param string $network Crypto network
     *
     * @return array Stats
     */

    public function get(string $network = ""): array
    {
        $http = new HTTPclient(BITINFOCHARTS_URL);
        $html = $http->get();

        $dom = new DOMDocument("1.0", "utf-8");
        @$dom->loadHTML(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
        $xpath = new DOMXPath($dom);
        $list  = $xpath->query("//div[@id='main_body']/table/tr");

        $table = [];
        foreach ($list as $element) {
            $table[] = $this->_getFields($dom->saveHTML($element));
        }

        $table = $this->_replacer($this->_convert($table));

        return $table;
    } //end get()


    /**
     * Get currencies table fields
     *
     * @param string $html Table row HTML
     *
     * @return array fields content
     */

    private function _getFields(string $html): array
    {
        $fields = [];

        $dom = new DOMDocument("1.0", "utf-8");
        @$dom->loadHTML(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
        $xpath = new DOMXPath($dom);
        $list  = $xpath->query("//td");
        foreach ($list as $element) {
            $fields[] = $element->textContent;
        }

        return $fields;
    } //end _getFields()


    /**
     * Convert table data to readable format
     *
     * @param array $table Table data to convert
     *
     * @return array Converted table
     */

    private function _convert(array $table): array
    {
        $converted = [];

        $i = 0;
        $j = 1;

        for ($j = 1; $j < count($table[0]); $j++) {
            $currency = [];

            for ($k = 1; ($k < count($table) - 1); $k++) {
                $currency[trim($table[$k][$i])] = trim($table[$k][$j]);
            }

            $converted[trim($table[$k][$j])] = $currency;
        } //end for

        return $converted;
    } //end _convert()


    /**
     * Fields name replacer
     *
     * @param array $table Table for conversion
     *
     * @return array Renamed table
     */

    private function _replacer(array $table): array
    {
        $newkeys = [
            0  => "total",
            1  => "price",
            2  => "marketcap",
            3  => "transactions_24h",
            4  => "transactions_1h",
            5  => "turnhover_24h",
            6  => "turnhover_1h",
            7  => "amount",
            8  => "amount_median",
            9  => "block_time",
            10 => "total_blocks",
            11 => "total_blocks_24h",
            12 => "total_blocks_1h",
            13 => "block_reward",
            14 => "block_reward_24h",
            15 => "difficulty",
            16 => "hashrate",
            17 => "mining_profit",
            //		    18 => "top_100_owners",
        ];

        $new = [];

        foreach ($table as $currency) {
            $newcurrency = [];
            $keys        = array_keys($currency);
            $name        = preg_replace("/[^A-Z]+/ui", "", $currency[$keys[0]]);

            for ($i = 0; $i < count($currency); $i++) {
                if (isset($newkeys[$i]) === true) {
                    $newcurrency[$newkeys[$i]] = $this->_getValue($currency[$keys[$i]], $newkeys[$i]);
                } //end if

            } //end for

            $new[$name] = $newcurrency;
        } //end foreach

        return $new;
    } //end _replacer()


    /**
     * Get savable standard value
     *
     * @param string $value Value for conversion
     * @param string $key   Array key of value
     *
     * @return string converted value
     */

    private function _getValue(string $value, string $key): string
    {
        $patterns = [
            "total"            => ["replace" => "/\D/u", "value" => "/(?P<value>[0-9,]+)\s[A-Z]+/ui"],
            "price"            => [
                "replace" => "/[^0-9.]/u",
                "value"   => "/1\s+[A-Z]+\s+[=]{1}\s+[$]{1}\s+(?P<value>[0-9,.]+)\s+USD.*/ui",
            ],
            "marketcap"        => ["replace" => "/\D/u", "value" => "/[$]{1}(?P<value>[0-9,]+)\s[A-Z]+/ui"],
            "transactions_24h" => ["replace" => "/\D/u", "value" => "/(?P<value>.+)/ui"],
            "transactions_1h"  => ["replace" => "/\D/u", "value" => "/(?P<value>.+)/ui"],
            "amount"           => ["replace" => "/[^0-9.]/u", "value" => "/(?P<value>[0-9,.]+)\s[A-Z]+.*/ui"],
            "amount_median"    => ["replace" => "/[^0-9.]/u", "value" => "/(?P<value>[0-9,.]+)\s[A-Z]+.*/ui"],
            "turnhover_24h"    => ["replace" => "/[^0-9.]/u", "value" => "/(?P<value>[0-9,.]+)\s[A-Z]+.*/ui"],
            "turnhover_1h"     => ["replace" => "/[^0-9.]/u", "value" => "/(?P<value>[0-9,.]+)\s[A-Z]+.*/ui"],
            "total_blocks"     => ["replace" => "/\D/u", "value" => "/(?P<value>.+)/ui"],
            "total_blocks_24h" => ["replace" => "/\D/u", "value" => "/(?P<value>.+)/ui"],
            "total_blocks_1h"  => ["replace" => "/\D/u", "value" => "/(?P<value>.+)/ui"],
        ];

        if (isset($patterns[$key]) === true) {
            if (preg_match($patterns[$key]["value"], $value, $result) > 0) {
                $value = preg_replace($patterns[$key]["replace"], "", $result["value"]);
            } //end if

        } else {
            switch ($key) {
                case "difficulty":
                    $value = $this->_calcDifficulty($value);
                    break;
                case "block_time":
                    $value = $this->_getTimeInSeconds($value);
                    break;
                case "block_reward":
                    $value = $this->_calcReward($value);
                    break;
                case "block_reward_24h":
                    $value = $this->_calcReward($value);
                    break;
                case "hashrate":
                    $value = $this->_calcHashrate($value);
                    break;
                case "mining_profit":
                    $value = $this->_calcMiningProfit($value);
                    break;
            } //end switch

        } //end if

        return $value;
    } //end _getValue()


    /**
     * Convert difficulty to stantard format
     *
     * @param string $difficultystring String to parse and convert
     *
     * @return float Converted difficulty
     */

    private function _calcDifficulty(string $difficultystring): float
    {
        if (preg_match("/(?P<value>[0-9,.]+)\s?(?P<degree>(K|M|G|T|P|E){1})?.*/ui", $difficultystring, $result) > 0) {
            $degrees = [
                "K" => 3,
                "M" => 6,
                "G" => 9,
                "T" => 12,
                "P" => 15,
                "E" => 18,
            ];

            $difficulty = (preg_replace("/[^0-9.]/u", "",
                    $result["value"]) * ((isset($result["degree"]) === true) ? pow(10,
                    $degrees[$result["degree"]]) : 1));
        } else {
            $difficulty = 0;
        } //end if

        return $difficulty;
    } //end _calcDifficulty()


    /**
     * Get time in seconds, convert string to fload seconds time
     *
     * @param string $timestring Time to convert
     *
     * @return float Converted time in seconds
     */

    private function _getTimeInSeconds(string $timestring): float
    {
        if (preg_match("/((?P<minutes>[0-9]+)m\s)?(?P<seconds>[0-9.]+)s/ui", $timestring, $result) > 0) {
            $time = ((isset($result["minutes"]) === true) ? ($result["minutes"] * 60) : 0) + $result["seconds"];
        } else {
            $time = 0;
        }

        return $time;
    } //end _getTimeInSeconds()


    /**
     * Caclulate block reward
     *
     * @param string $rewardstring String to parse and calculate
     *
     * @return float Calculated result
     */

    private function _calcReward(string $rewardstring): float
    {
        if (preg_match("/(?<coinbase>[0-9,.]+)\+(-)?(?P<comission>[0-9.,]+).*/ui", $rewardstring, $result) > 0) {
            $reward = ($result["coinbase"] + $result["comission"]);
        } else {
            $reward = 0;
        } //end if

        return $reward;
    } //end _calcReward()


    /**
     * Convert hashrate to standard format
     *
     * @param string $hashratestring Hashrate string to convert
     *
     * @return float converted hashrate
     */

    private function _calcHashrate(string $hashratestring): float
    {
        if (preg_match("/(?P<hashrate>[0-9,.]+)\s(?P<degree>[A-Za-z]+)\/s.*/ui", $hashratestring, $result) > 0) {
            $degrees = [
                "Mhash" => 6,
                "Ghash" => 9,
                "Thash" => 12,
                "Phash" => 15,
                "Ehash" => 18,
            ];

            $hashrate = $result["hashrate"] * pow(10, $degrees[$result["degree"]]);
        } else {
            $hashrate = 0;
        } //end if

        return $hashrate;
    } //end _calcHashrate()


    /**
     * Convert mining profit to standard format (in USD for 1 Hash/s)
     *
     * @param string $profitstring Mining profit string
     *
     * @return float Moning profit in USD for 1 Hash/s
     */

    private function _calcMiningProfit(string $profitstring): float
    {
        if (preg_match("/(?P<value>[0-9,.]+)\s+USD\/Day\s+for\s+1\s+(?P<degree>[A-Za-z]+)\/s/ui", $profitstring,
                $result) > 0) {
            $degrees = [
                "Hash"  => 0,
                "KHash" => -3,
                "MHash" => -6,
                "GHash" => -9,
                "THash" => -12,
                "PHash" => -15,
                "EHash" => -18,
            ];

            $profit = ($result["value"] * pow(10, $degrees[$result["degree"]]));
        } else {
            $profit = 0;
        }

        return $profit;
    } //end _calcMiningProfit()


} //end class

?>
