<?php

/**
 * PHP version 7.1
 *
 * @package AM\Networkstat
 */

namespace Test;

/**
 * BTC.com MOCK responder
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-03-05 16:33:47 +0300 (Mon, 05 Mar 2018) $ $Revision: 13 $
 * @link    $HeadURL: https://svn.btcdaily.ru/networkstat/trunk/tests/mock/btc/stats/pool/index.php $
 */

if (isset($_REQUEST["pool_mode"]) === true) {
    echo file_get_contents(__DIR__ . "/" . $_REQUEST["pool_mode"] . "/index.html");
} else {
    if (isset($_REQUEST["percent_mode"]) === true) {
        echo file_get_contents(__DIR__ . "/" . $_REQUEST["percent_mode"] . "/index.html");
    }
}

?>
