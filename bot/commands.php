<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Channel\Channel;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Activity;

require_once 'required_functions.php';

    // Check all messages
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {

        // Check if BOT is dev status or live.
        if($_ENV['STATUS'] == 'dev') {
            $allowedChannels = [
                $_ENV['DEV_BOT_REQ'],
            ];
        } else {
        // Live Bot Channels
        $allowedChannels = [
            '1045223017518743602', //devops
            '1037188637000994899', //general
            '1125445580794110013', //bot-requests
            ];
        }
        // Check if the message is from an allowed channel
        if (!in_array($message->channel_id, $allowedChannels)) {
            return;
        }
        // Ignore Bot Messages
        if ($message->author->id == $discord->user->id) {
            return;
        }

        ## ---- COMMANDS ---- ##

        // !help
        if ($message->content == "!help") {
            // Get the user who sent the message
            $user = $message->author;
        
            // Create and populate the embed message with details
            $embed = [
                'title' => 'Available Commands',
                'description' => 'Here are some of my available commands. Not all might be available to you.',
                'footer' => [
                    'text' => 'Horizon Tutoring Service',
                ],
                'fields' => [
                    [
                        'name' => '!help',
                        'value' => 'Displays details of all commands available to you.',
                    ],
                    [
                        'name' => '!shutdown',
                        'value' => 'Shuts down the bot functions. Auto restart every hour.',
                    ],
                    [
                        'name' => '!ping',
                        'value' => 'Pings the bot for a response.',
                    ],
                    
                    // Add more commands here as needed
                ],
            ];
        
            // Send the help message as a private message to the user
            $user->getPrivateChannel()->done(function (Channel $channel) use ($user, $embed, $message, $discord) {
                // Send the help message
                $user->sendMessage('', false, $embed)->done(function () use ($user, $message, $discord) {

                    SendBotLog(
                        $discord, 
                        'COMMAND', 
                        '`!help` command used by ' . $user->username,
                    );

                    $message->delete();
                });
            });
        }

        // !ping
        if ($message->content == '!ping') {
            // Delete the initial command message
            $message->delete();
        
            // Send the response message
            $channel = $message->channel;
            $channel->sendMessage('<@' . $message->author->id . '> pong!')->done(function (Message $responseMessage) use ($discord, $message){
                SendBotLog(
                    $discord, 
                    'COMMAND', 
                    '`!ping` command used by ' . $message->author->username,
                );

                sleep(5);

                $responseMessage->delete();
            });
            return;
        }

        // !shutdown
        if ($message->content == '!shutdown' && $message->author->id == '200426385863344129') {
            $message->delete();
            
            $channel = $message->channel;
            $channel->sendMessage('Understood <@' . $message->author->id . '>, Bot is now shutting down...')->done(function (Message $responseMessage) use ($discord, $message) {
                sleep(5);

                $responseMessage->delete();

                // Find the channel by its ID
                $channel = $discord->getChannel($_ENV['SERVER_LOGS']);
        
                // Send the message in the channel
                $channel->sendMessage('**[' . date('H:i:s') . ' - SHUTDOWN]** Bot Service Stopping. Bot will auto restart next hour.')
                    ->done(function () use ($discord) {
                        // Delay closing the connection for 2 seconds
                        sleep(2);
        
                        $discord->updatePresence(null, false, 'invisible', false);

                        sleep(3);
                        
                        $discord->close();
                    });
                
            });

            
            return;
        }

        // !notify
        if($message->content == "!notify"){
            $message->delete();

            $channel = $message->channel;
            $channel->sendMessage('<@' . $message->author->id . '> pong!')->done(function (Message $responseMessage) use ($discord, $message) {
                SendBotLog(
                    $discord, 
                    'COMMAND', 
                    '`!notify` command used by ' . $message->author->username,
                );

                sleep(5);

                $responseMessage->delete();
            });
        }

    });