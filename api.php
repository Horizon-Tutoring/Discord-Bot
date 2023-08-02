<?php

$botRunning = __DIR__.'/bot-script.lock';
$apiRunning = __DIR__.'/api-script.lock';

// Check that the API Script is not still running
if (file_exists($botRunning)) {

    // Check the PID stored in the lock file
    $pid = intval(file_get_contents($botRunning));

    // Check if the process with the stored PID is still running
    if (isBotRunning($pid)) {
        // Another instance is running, exit the current script
        echo "[CHECK] Horizon Bot Service is running.\n\n";

    } else {
        echo "[SYSTEM] Horizon Bot Service is not currently running. Exiting script. \n\n";
        exit;
    }
}

// Check that the API Script is not still running
if (file_exists($apiRunning)) {

    // Check the PID stored in the lock file
    $pid2 = intval(file_get_contents($apiRunning));

    // Check if the process with the stored PID is still running
    if (isProcessRunning($pid2)) {
        // Another instance is running, terminate the previous script
        echo "[SYSTEM] The Horizon Tutoring Bot is still running on the server. Terminating previous script.\n";

        // Attempt to terminate the previous script
        if (posix_kill($pid2, SIGTERM)) {
            echo "[SYSTEM] Previous script terminated successfully.\n";
        } else {
            echo "[SYSTEM] Failed to terminate the previous script.\n";
            exit;
        }

        // Remove the lock file
        unlink($apiRunning);

    } else {
        // The lock file exists, but the previous script has terminated. Remove the lock file.
        unlink($apiRunning);
    }
}

// Create the lock file
file_put_contents($apiRunning, getmypid());

echo "[SYSTEM] API code not currently running. Checking for any required messages. \n \n";

include __DIR__.'/vendor/autoload.php';
require_once 'api/required_functions.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Activity;
use Discord\Parts\Embed\Embed;

// Ability to use ENV File.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

date_default_timezone_set('Australia/Brisbane');

// Script Set to run for 60 seconds. Thence terminate the bot.
ini_set('max_execution_time', 60);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiBaseUrl = $_ENV['API_ENDPOINT'];
$httpClient = new GuzzleHttp\Client();

    try {
        // Make a GET request to the API endpoint
        $response = $httpClient->get($apiBaseUrl, [
            'headers' => [
                'X-API-KEY' => $_ENV['API_KEY'],
            ],
        ]);

       // Decode the JSON response body and ensure it's always an array
        $discord_results = (array) json_decode($response->getBody(), true);
        
        // Convert the array elements to objects for usage
        foreach ($discord_results as $key => $value) {
            $discord_results[$key] = (object) $value;
        }

        print_r($discord_results);

        // Create IDs that need to be updated.
        $api_ids = [];
        foreach($discord_results as $results) {
            $api_ids[] = $results->id;
        }

        // print_r($api_ids);

        // // Send a POST request to update the 'completed' attribute (optional)
        // $httpClient->post($apiBaseUrl, [
        //     'form_params' => [
        //         'completed' => true,
        //     ],
        // ]);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }


if(count($discord_results) > 0) {
    $discord = new Discord([
        'token' => $_ENV['BOT_TOKEN'],
        'intents' => Intents::getDefaultIntents()
    //      | Intents::MESSAGE_CONTENT, // Note: MESSAGE_CONTENT is privileged, see https://dis.gd/mcfaq
    ]);
    
    $discord->on('ready', function (Discord $discord) use ($discord_results, $api_ids){   

        foreach($discord_results as $content){
            if($content->channel == '1') {
                $m_channel="WEB_NOTIFICATIONS";
            }

            if($content->channel == '2') {
                $m_channel="SERVER_LOGS";
            }

            if($content->channel == '3') {
                $m_channel="BOT_LOGS";
            }

            if($content->channel == '4') {
                $m_channel="ERROR_LOGS";
            }
            
            // TYPE IDENTIFICATION
            // 1=EMBED
            // 2=GENERAL TXT

            // CHANNEL IDENTIFICATION
            // 1 = Web Notifications
            // 2 = Server Logs
            // 3 = Bot Logs
            // 4 = Error Logging

            #CONTENT TYPE 1 --- EMBED OPTION
            if($content->type == "1"){
                if($content->message == null){
                    $message = '';
                } else {
                    $message = $content->message;
                }

                if($content->url == null){
                    $url = '';
                } else {
                    $url = $content->url;
                }


                // Get Channel based of ENV file
                $channel = $discord->getChannel($_ENV[$m_channel]);

                // Create Embed Message based of GET Request
                SendEmbedMessage(
                    $discord,
                    $channel,
                    $content->title,
                    $message,
                    $url,
                    $content->content,
                    $content->color,
                    $content->footer
                );
            }

            #CONTENT TYPE 2
            if($content->type == "2"){
                $channel = $discord->getChannel($_ENV[$m_channel]);
                    
                // Send the message in the channel
                $channel->sendMessage('**[' . date('H:i:s') . ' - ' . $content->title . ']** ' . $content->content);
            }
        }

         // Create a BotLog that Message was completed.
         SendBotLog(
            $discord, 
            'API', 
            count($discord_results) . ' action(s) have been completed'
        );

        // Update all DB entries so they do not get sent more than once.
        $apiBaseUrl = $_ENV['API_ENDPOINT'];
        $httpClientPost = new GuzzleHttp\Client();
        
        try {
            // Construct the POST request with the bulk update data
            $httpClientPost->post($apiBaseUrl, [
                'headers' => [
                    'X-API-KEY' => $_ENV['API_KEY'],
                ],
                'json' => $api_ids,
            ]);
        } catch (Exception $e) {
            echo "Error updating entries: " . $e->getMessage() . "\n";
        }
    });
    
    $discord->run();
} else {
    echo "[RESULT] No actions required. Exiting Script.\n\n";
    exit();
}

// Function to check if process is running
function isBotRunning($pid) {
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

function isProcessRunning($pid2) {
    try {
        // Sending a signal 0 to the process to check if it's running
        return posix_kill($pid2, 0);
    } catch (Exception $e) {
        return false;
    }
}