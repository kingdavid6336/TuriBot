<?php

require_once 'functions/updates.php';
require_once 'base_functions.php';


if (!function_exists('curl_version')) {
    exit('You must install or enable php-curl');
}


//security check
//var_dump($_POST['yes'], $_POST['api'], $_POST['link'], $_POST['connections'], $_POST['no'], $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
if (isset($_POST['yes']) and $_POST['yes'] !== '') {
    exit('Error, invalid parameter');
}

if (isset($_POST['no']) and $_POST['no'] !== '') {
    exit('Error, invalid parameter');
}

if (isset($_POST['api'])) {
    $token = explode(":", $_POST['api']);
    if ((!is_numeric($token[0])) or
        (!(sizeof($token) === 2)) or
        (!(preg_match_all('/[a-zA-Z0-9_-]/', $token[1], $matches, PREG_SET_ORDER, 0) === strlen($token[1])))) {
        exit('Invalid token');
    }
}

if (isset($_POST['link'])) {
    if (!(stripos($_POST['link'], 'https://') === 0)) {
        exit('The link must have https://');
    }
}

if (isset($_POST['connections']) and !((1 <= $_POST['connections']) and ($_POST['connections'] <= 100))) {
    exit('The connections parameter must be a number between 1 and 100');
}


echo '<form action="setup.php" method="POST">';

if (isset($_POST['yes'])) {

    if (isset($_POST['link'])) {
        $link = strip_tags($_POST['link']);
    } else {
        $link = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $explode = explode('setup.php', $link);
        $link = $explode[0] . 'commands.php';
    }

    echo '<p><input type="hidden" name="link" value="' . htmlspecialchars($link) . '" /></p>';
    echo '<p>Input API Token: <input type="text" name="api" value="" style="width:400px;" /></p>';
    echo '<p>Example: 123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11</p>';
    echo '<p><button type="submit" name="submit">Submit</button></p>';

} elseif (isset($_POST['connections'])) {

    $api = $_POST['api'];
    $link = strip_tags($_POST['link']);
    $connections = $_POST['connections'];
    $link = $link . '?api=' . $api;
    $responseWebhook = setWebhook($api, $link, null, $connections);
    $response = getMeApi($api);

    if (($responseWebhook['description'] == 'Webhook was set' or $responseWebhook['description'] == 'Webhook is already set') and $response['ok'] == true) {
        $username = $response['result']['username'];
        echo 'Setup successful: <a href="http://t.me/' . htmlspecialchars($username) . '"> @' . htmlspecialchars($username) . '</a>';
    } else {
        echo '<p>Setup failed: API TOKEN wrong or impossible to connect to Telegram</p>';
        echo '<p>' . $response['result']['description'] . '</p>';
        echo '<p>Click here to try the setup again: <button type="submit" name="reset">Reset</button></p>';
    }

} elseif (isset($_POST['api'])) {

    $api = $_POST['api'];
    $link = strip_tags($_POST['link']);

    echo '<p><input type="hidden" name="api" value="' . htmlspecialchars($api) . '" /></p>';
    echo '<p><input type="hidden" name="link" value="' . htmlspecialchars($link) . '" /></p>';
    echo '<p>Input max connections: <input type="number" name="connections" min="0" max="100" value="100"></p>';
    echo '<p>Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Defaults of Bot APIs is 40. Use lower values to limit the load on your bot‘s server, and higher values to increase your bot’s throughput.</p>';
    echo '<p><button type="submit" name="submit">Submit</button>';
    echo '<button type="submit" name="skip">Skip</button></p>';

} elseif (isset($_POST['no'])) {

    echo '<p>Input Link: <input type="text" name="link" value="" /></p>';
    echo '<p>HTTPS link is required!</p>';
    echo '<p>Example: https://mysite.com/bot/command.php</p>';
    echo '<p><button type="submit" name="yes">Submit</button></p>';

} else {

    $actual_link = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $explode = explode('setup.php', $actual_link);

    echo '<p>Is the link correct?</p>';
    echo '<p>' . htmlspecialchars($explode[0]) . 'commands.php</p>';
    echo '<p><button type="submit" name="yes">Yes</button>';
    echo '<button type="submit" name="no">No</button></p>';

}

echo '</form>';