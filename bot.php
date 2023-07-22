<?php

$lockFile = __DIR__.'/bot-script.lock';

// Check if lock file exists
if (file_exists($lockFile)) {

    // Check the PID stored in the lock file
    $pid = intval(file_get_contents($lockFile));

    // Check if the process with the stored PID is still running
    if (isProcessRunning($pid)) {
        // Another instance is running, exit the current script
        echo "[SYSTEM] The Horizon Tutoring Bot is still running on the server. Exiting restart script.\n";
        exit;

    } else {
        // The lock file exists, but the previous script has terminated. Remove the lock file.
        unlink($lockFile);
    }
}

// Create the lock file
file_put_contents($lockFile, getmypid());

echo "[SYSTEM] The bot is not currently running. The service is now being started! \n \n";

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Activity;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

date_default_timezone_set('Australia/Brisbane');

$discord = new Discord([
    'token' => $_ENV['BOT_TOKEN'],
    'intents' => Intents::getDefaultIntents()
//      | Intents::MESSAGE_CONTENT, // Note: MESSAGE_CONTENT is privileged, see https://dis.gd/mcfaq
]);

$discord->on('ready', function (Discord $discord) {
    echo "[SYSTEM] The bot is now operating. Dispensing Activation Message in #server-logs \n \n", PHP_EOL;

    // Find the channel by its ID
    $channel = $discord->getChannel($_ENV['BOT_LOGS']);
    
    // Send the message in the channel
    $channel->sendMessage('**[' . date('H:i:s') . ' - SETUP]** Bot Service Started');

    $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
        'name' => $_ENV['ACTIVITY'],
        'type' => \Discord\Parts\User\Activity::TYPE_LISTENING,
    ]);
    
    $discord->updatePresence($activity, false, 'online', false);

    include __DIR__.'/bot/commands.php';
});

$discord->run();

function isProcessRunning($pid) {
    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
        // Windows
        exec("tasklist /FI \"PID eq $pid\"", $output);
        return count($output) > 1;
    } else {
        // Unix-like systems
        $output = [];
        exec("ps -p $pid", $output);
        return count($output) > 1;
    }
}

// Remove the lock file before exiting
unlink($lockFile);
