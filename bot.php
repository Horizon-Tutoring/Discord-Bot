<?php

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Activity;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord->close();

$discord = new Discord([
    'token' => $_ENV['BOT_TOKEN'],
    'intents' => Intents::getDefaultIntents()
//      | Intents::MESSAGE_CONTENT, // Note: MESSAGE_CONTENT is privileged, see https://dis.gd/mcfaq
]);

$discord->on('ready', function (Discord $discord) {
    echo "Bot is ready!", PHP_EOL;

    $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
        'name' => '!shutdown',
        'type' => \Discord\Parts\User\Activity::TYPE_LISTENING,
    ]);
    
    $discord->updatePresence($activity, false, 'online', false);

    include __DIR__.'/commands/messages.php';
});

$discord->run();