<?php require_once __DIR__ .  '/../commons/top.php'; ?>

  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta http-equiv="refresh" content="2;URL='/ethbtc/'">

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/">Home</a></li>
      <li class="breadcrumb-item active">ETHBTC</li>
    </ol>
  </nav>

<?php

  $orderbook = array("https://api.binance.com/api/v1/depth?symbol=ETHBTC", "https://openapi.bitmart.com/v2/symbols/ETH_BTC/orders?precision=6", "https://apiv2.bitz.com/Market/depth?symbol=eth_btc", "https://api.cobinhood.com/v1/market/orderbooks/ETH-BTC?limit=1", "https://api.hitbtc.com/api/2/public/orderbook/ETHBTC?limit=1", /*"https://api.kraken.com/0/public/Depth?pair=XETHXXBT",*/ "https://api.kucoin.com/v1/open/orders?symbol=ETH-BTC&limit=1");
  // array of curl handles
  $multiCurl = array();
  // data to be returned
  $result = array();
  // multi handle
  $mh = curl_multi_init();

  foreach ($orderbook as $i => $url) {

    $multiCurl[$i] = curl_init();
    curl_setopt($multiCurl[$i], CURLOPT_URL, $url);
	  curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
    curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($multiCurl[$i], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_multi_add_handle($mh, $multiCurl[$i]);
  }

  $index=null;

  do {
    curl_multi_exec($mh,$index);
  } while($index > 0);

  foreach($multiCurl as $k => $ch) {
    $result[$k] = curl_multi_getcontent($ch);
    curl_multi_remove_handle($mh, $ch);
  }

  $binance = json_decode($result[0], true);
  $bitmart = json_decode($result[1], true);
  $bitz = json_decode($result[2], true);
  $cobinhood = json_decode($result[3], true);
  $hitbtc = json_decode($result[4], true);
  //$kraken = json_decode($result[5], true);
  $kucoin = json_decode($result[5], true);

  $binSellPrice = $binance['bids'][0][0] - $binance['bids'][0][0] * 0.1 / 100;
  $binSellQuantity = $binance['bids'][0][1];
  $binBuyPrice = $binance['asks'][0][0] + $binance['asks'][0][0] * 0.1 / 100;
  $binBuyQuantity = $binance['asks'][0][1];

  $bitSellPrice = $bitmart['buys'][0]['price'] - $bitmart['buys'][0]['price'] * 0.05 / 100;
  $bitSellQuantity = $bitmart['buys'][0]['amount'];
  $bitBuyPrice = $bitmart['sells'][0]['price'] + $bitmart['sells'][0]['price'] * 0.05 / 100;
  $bitBuyQuantity = $bitmart['sells'][0]['amount'];

  $bitzSellPrice = $bitz['data']['bids'][0][0] - $bitz['data']['bids'][0][0] * 0.1 / 100;
  $bitzSellQuantity = $bitz['data']['bids'][0][1];
  $bitzAskArray = $bitz['data']['asks'];
  $bitzBuyQuantity = end($bitzAskArray)[1];
  /*if($bitzBuyQuantity < 0.05) {
    $bitzBuyQuantity = array_values(array_slice($bitzAskArray, -2))[1];
    $bitzBuyPrice = array_values(array_slice($bitzAskArray, -2))[0] + array_values(array_slice($bitzAskArray, -2))[0] * 0.1 / 100;
    echo $bitzBuyPrice;
  }
  else {*/
    $bitzBuyPrice = end($bitzAskArray)[0] + end($bitzAskArray)[0] * 0.1 / 100;
  //}

  $cobSellPrice = $cobinhood['result']['orderbook']['bids'][0][0];
  $cobSellQuantity = $cobinhood['result']['orderbook']['bids'][0][2];
  $cobBuyPrice = $cobinhood['result']['orderbook']['asks'][0][0];
  $cobBuyQuantity = $cobinhood['result']['orderbook']['asks'][0][2];

  $hitSellPrice = $hitbtc['bid'][0]['price'] - $hitbtc['bid'][0]['price'] * 0.1 / 100;
  $hitSellQuantity = $hitbtc['bid'][0]['size'];
  $hitBuyPrice = $hitbtc['ask'][0]['price'] + $hitbtc['ask'][0]['price'] * 0.1 / 100;
  $hitBuyQuantity = $hitbtc['ask'][0]['size'];

  /*$kraSellPrice = $kraken['result']['XETHXXBT']['bids'][0][0] - $kraken['result']['XETHXXBT']['bids'][0][0] * 0.26 / 100;
  $kraSellQuantity = $kraken['result']['XETHXXBT']['bids'][0][1];
  $kraBuyPrice = $kraken['result']['XETHXXBT']['asks'][0][0] + $kraken['result']['XETHXXBT']['asks'][0][0] * 0.26 / 100;
  $kraBuyQuantity = $kraken['result']['XETHXXBT']['asks'][0][1];*/

  $kucSellPrice = $kucoin['data']['BUY'][0][0] - $kucoin['data']['BUY'][0][0] * 0.1 / 100;
  $kucSellQuantity = $kucoin['data']['BUY'][0][1];
  $kucBuyPrice = $kucoin['data']['SELL'][0][0] + $kucoin['data']['SELL'][0][0] * 0.1 / 100;
  $kucBuyQuantity = $kucoin['data']['SELL'][0][1];

?>


<h2>Binance</h2>
<?php
  //Binance-Cobinhood
  $quantity = number_format(min($binSellQuantity, $cobBuyQuantity), 4);
  $res = $quantity * $binSellPrice - $quantity * $cobBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Binance Sell Price</th>
        <th style="width:300px" scope="col">Cobinhood Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $binSellPrice; ?></td>
        <td><?php echo $cobBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Binance-Bitmart
  $quantity = number_format(min($binSellQuantity, $bitBuyQuantity), 4);
  $res = $quantity * $binSellPrice - $quantity * $bitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Binance Sell Price</th>
        <th style="width:300px" scope="col">Bitmart Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $binSellPrice; ?></td>
        <td><?php echo $bitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Binance-Bitz
  $quantity = number_format(min($binSellQuantity, $bitzBuyQuantity), 4);
  $res = $quantity * $binSellPrice - $quantity * $bitzBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Binance Sell Price</th>
        <th style="width:300px" scope="col">Bitz Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $binSellPrice; ?></td>
        <td><?php echo $bitzBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Binance-Hitbtc
  $quantity = number_format(min($binSellQuantity, $hitBuyQuantity), 4);
  $res = $quantity * $binSellPrice - $quantity * $hitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Binance Sell Price</th>
        <th style="width:300px" scope="col">Hitbtc Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $binSellPrice; ?></td>
        <td><?php echo $hitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Binance-Kucoin
  $quantity = number_format(min($binSellQuantity, $kucBuyQuantity), 4);
  $res = $quantity * $binSellPrice - $quantity * $kucBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Binance Sell Price</th>
        <th style="width:300px" scope="col">Kucoin Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $binSellPrice; ?></td>
        <td><?php echo $kucBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>


<h2>Bitmart</h2>
<?php
  //Bitmart-Binance
  $quantity = number_format(min($bitSellQuantity, $binBuyQuantity), 4);
  $res = $quantity * $bitSellPrice - $quantity * $binBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Bitmart Sell Price</th>
        <th style="width:300px" scope="col">Binance Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $bitSellPrice; ?></td>
        <td><?php echo $binBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Bitmart-Bitz
  $quantity = number_format(min($bitSellQuantity, $bitzBuyQuantity), 4);
  $res = $quantity * $bitSellPrice - $quantity * $bitzBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Bitmart Sell Price</th>
        <th style="width:300px" scope="col">Bitz Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $bitSellPrice; ?></td>
        <td><?php echo $bitzBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Bitmart-Cobinhood
  $quantity = number_format(min($bitSellQuantity, $cobBuyQuantity), 4);
  $res = $quantity * $bitSellPrice - $quantity * $cobBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Bitmart Sell Price</th>
        <th style="width:300px" scope="col">Cobinhood Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $bitSellPrice; ?></td>
        <td><?php echo $cobBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Bitmart-Hitbtc
  $quantity = number_format(min($bitSellQuantity, $hitBuyQuantity), 4);
  $res = $quantity * $bitSellPrice - $quantity * $hitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Bitmart Sell Price</th>
        <th style="width:300px" scope="col">Hitbtc Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $bitSellPrice; ?></td>
        <td><?php echo $hitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Bitmart-Kucoin
  $quantity = number_format(min($bitSellQuantity, $kucBuyQuantity), 4);
  $res = $quantity * $bitSellPrice - $quantity * $kucBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Bitmart Sell Price</th>
        <th style="width:300px" scope="col">Kucoin Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $bitSellPrice; ?></td>
        <td><?php echo $kucBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>


<h2>Cobinhood</h2>
<?php
  //Cobinhood-Binance
  $quantity = number_format(min($cobSellQuantity, $binBuyQuantity), 4);
  $res = $quantity * $cobSellPrice - $quantity * $binBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Cobinhood Sell Price</th>
        <th style="width:300px" scope="col">Binance Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $cobSellPrice; ?></td>
        <td><?php echo $binBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Cobinhood-Bitmart
  $quantity = number_format(min($cobSellQuantity, $bitBuyQuantity), 4);
  $res = $quantity * $cobSellPrice - $quantity * $bitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Cobinhood Sell Price</th>
        <th style="width:300px" scope="col">Bitmart Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $cobSellPrice; ?></td>
        <td><?php echo $bitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Cobinhood-Bitz
  $quantity = number_format(min($cobSellQuantity, $bitzBuyQuantity), 4);
  $res = $quantity * $cobSellPrice - $quantity * $bitzBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Cobinhood Sell Price</th>
        <th style="width:300px" scope="col">Bitz Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $cobSellPrice; ?></td>
        <td><?php echo $bitzBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Cobinhood-Hitbtc
  $quantity = number_format(min($cobSellQuantity, $hitBuyQuantity), 4);
  $res = $quantity * $cobSellPrice - $quantity * $hitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Cobinhood Sell Price</th>
        <th style="width:300px" scope="col">Hitbtc Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $cobSellPrice; ?></td>
        <td><?php echo $hitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Cobinhood-Kucoin
  $quantity = number_format(min($cobSellQuantity, $kucBuyQuantity), 4);
  $res = $quantity * $cobSellPrice - $quantity * $kucBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Cobinhood Sell Price</th>
        <th style="width:300px" scope="col">Kucoin Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $cobSellPrice; ?></td>
        <td><?php echo $kucBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>


<h2>Hitbtc</h2>
<?php
  //Hitbtc-Binance
  $quantity = number_format(min($hitSellQuantity, $binBuyQuantity), 4);
  $res = $quantity * $hitSellPrice - $quantity * $binBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Hitbtc Sell Price</th>
        <th style="width:300px" scope="col">Binance Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $hitSellPrice; ?></td>
        <td><?php echo $binBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Hitbtc-Bitmart
  $quantity = number_format(min($hitSellQuantity, $bitBuyQuantity), 4);
  $res = $quantity * $hitSellPrice - $quantity * $bitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Hitbtc Sell Price</th>
        <th style="width:300px" scope="col">Bitmart Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $hitSellPrice; ?></td>
        <td><?php echo $bitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Hitbtc-Cobinhood
  $quantity = number_format(min($hitSellQuantity, $cobBuyQuantity), 4);
  $res = $quantity * $hitSellPrice - $quantity * $cobBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Hitbtc Sell Price</th>
        <th style="width:300px" scope="col">Cobinhood Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $binSellPrice; ?></td>
        <td><?php echo $hitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Hitbtc-Kucoin
  $quantity = number_format(min($hitSellQuantity, $kucBuyQuantity), 4);
  $res = $quantity * $hitSellPrice - $quantity * $kucBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Hitbtc Sell Price</th>
        <th style="width:300px" scope="col">Kucoin Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $hitSellPrice; ?></td>
        <td><?php echo $kucBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>


<h2>Kucoin</h2>
<?php
  //Kucoin-Binance
  $quantity = number_format(min($kucSellQuantity, $binBuyQuantity), 4);
  $res = $quantity * $kucSellPrice - $quantity * $binBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Kucoin Sell Price</th>
        <th style="width:300px" scope="col">Binance Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $kucSellPrice; ?></td>
        <td><?php echo $binBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Kucoin-Bitmart
  $quantity = number_format(min($kucSellQuantity, $bitBuyQuantity), 4);
  $res = $quantity * $kucSellPrice - $quantity * $bitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Kucoin Sell Price</th>
        <th style="width:300px" scope="col">Bitmart Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $kucSellPrice; ?></td>
        <td><?php echo $bitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Kucoin-Cobinhood
  $quantity = number_format(min($kucSellQuantity, $cobBuyQuantity), 4);
  $res = $quantity * $kucSellPrice - $quantity * $cobBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Kucoin Sell Price</th>
        <th style="width:300px" scope="col">Cobinhood Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $kucSellPrice; ?></td>
        <td><?php echo $cobBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  //Kucoin-Hitbtc
  $quantity = number_format(min($kucSellQuantity, $hitBuyQuantity), 4);
  $res = $quantity * $kucSellPrice - $quantity * $hitBuyPrice;
  $perc = $res * 100 / $quantity;
?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <td style="width:200px"></td>
        <th style="width:300px" scope="col">Kucoin Sell Price</th>
        <th style="width:300px" scope="col">Hitbtc Buy Price</th>
        <th style="width:300px" scope="col">Result</th>
        <th style="width:300px" scope="col">Percentage (%)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>Price</th>
        <td><?php echo $kucSellPrice; ?></td>
        <td><?php echo $hitBuyPrice; ?></td>
        <td class="res" rowspan="2"><?php echo number_format($res, 8); ?></td>
        <td class="per" rowspan="2"><?php echo number_format($perc, 5); ?></td>
      </tr>
      <tr>
        <th>Amount</th>
        <td><?php echo $quantity; ?></td>
        <td><?php echo $quantity; ?></td>
      </tr>
    </tbody>
  </table>

<?php
  // close
  curl_multi_close($mh);
  require_once __DIR__ .  '/../commons/bottom.php';
?>