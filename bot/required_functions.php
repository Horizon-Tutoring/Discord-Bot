<?php

function SendBotLog($discord, $type, $details) {
    $channel = $discord->getChannel($_ENV['BOT_LOGS']);

    $channel->sendMessage('**[' . date('H:i:s') . ' - ' . $type . ']** ' . $details . '.');
}