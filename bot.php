<?php

$lockFile = __DIR__.'/script.lock';

// Check if lock file exists
if (file_exists($lockFile)) {

    // Check if the script is still running
    $pid = intval(file_get_contents($lockFile));
    if (posix_kill($pid, 0)) {
        // Another instance is running, exit the current script
        echo "The Horizon Tutoring Bot is still running on the server. Exiting restart script.\n";
        exit;

    } else {
        // The lock file exists, but the previous script has terminated. Remove the lock file.
        unlink($lockFile);
    }
}

// Create the lock file
file_put_contents($lockFile, getmypid());

echo "[SYSTEM]\nThe bot is not currently running. The service is now being started! \n \n";

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Activity;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$discord = new Discord([
    'token' => $_ENV['BOT_TOKEN'],
    'intents' => Intents::getDefaultIntents()
//      | Intents::MESSAGE_CONTENT, // Note: MESSAGE_CONTENT is privileged, see https://dis.gd/mcfaq
]);

$discord->on('ready', function (Discord $discord) {
    echo "[SYSTEM]\nThe bot is now operating. Dispensing Activation Message in #server-logs \n \n", PHP_EOL;

    // Find the channel by its ID
    $channel = $discord->getChannel(1096629948363571291);
    
    // Send the message in the channel
    $channel->sendMessage(date('[Y-m-d H:i:s]') . 'The Horizon Tutoring Bot has now been started');

    $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
        'name' => '!shutdown',
        'type' => \Discord\Parts\User\Activity::TYPE_LISTENING,
    ]);
    
    $discord->updatePresence($activity, false, 'online', false);

    include __DIR__.'/commands/messages.php';
});

$discord->run();

// Remove the lock file before exiting
unlink($lockFile);
