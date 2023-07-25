<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Activity;
use Discord\Parts\Embed\Embed;
use Dotenv\Dotenv;

// Get the path to the parent folder (one level up from the current directory)
$parentFolderPath = dirname(__DIR__);

// Load the .env file from the parent folder
$dotenv = Dotenv::createImmutable($parentFolderPath);
$dotenv->load();

// Functions used by the bot throughout the below.
function SendBotLog($discord, $type, $details) {
    $channel = $discord->getChannel($_ENV['BOT_LOGS']);

    $channel->sendMessage('**[' . date('H:i:s') . ' - ' . $type . ']** ' . $details . '.');
}

function SendEmbedMessage($discord, $channel, $title, $message, $url, $description, $color, $footer) {
    $embed = new Embed($discord);

    // Set the title, description (content), color, and footer for the embed
    $embed
        ->setTitle($title)
        ->setUrl($url)
        ->setDescription($description)
        ->setColor($color) // You can specify a color in decimal format (e.g., 0xFF0000 for red)
        ->setFooter($footer);


                
    // Send the embed message in the channel
    $channel->sendMessage($message, false, $embed);
}