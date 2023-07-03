<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\User\Activity;

    // Check all messages
    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {

        // CHANNELS ALLOWED
        $allowedChannels = [
        '1045223017518743602', //devops
        '1037188637000994899' //general
        ];

        // Check if the message is from an allowed channel
        if (!in_array($message->channel_id, $allowedChannels)) {
            return;
        }

        // Ignore Bot Messages
        if ($message->author->id == $discord->user->id) {
            return;
        }


        // Shutdown Bot Code
        if ($message->content == '!shutdown' && $message->author->id == '200426385863344129') {
            $message->reply('Understood Joshua, Shutting down...')->done(function (Message $message) use ($discord) {

                $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
                    'name' => 'Shutting Down Simulator',
                    'type' => \Discord\Parts\User\Activity::TYPE_LISTENING,
                ]);

                $discord->updatePresence(null, false, 'invisible', false);
                
                $discord->close();
            });
            return;
        }

        

    });