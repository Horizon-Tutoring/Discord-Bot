<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\User\Member;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\Guild;
use Discord\WebSockets\Intents;
use Discord\WebSockets\Event;
use Discord\Parts\WebSockets\MessageReaction;

require_once 'required_functions.php';

    // MESSAGE ACTION ADDED
    $discord->on(Event::MESSAGE_REACTION_ADD, function (MessageReaction $reaction, Discord $discord) {
        echo "Reaction Added. Checking Defined Rules. ", PHP_EOL;

        if($reaction->message_id === $_ENV['NOTIFY_MESSAGE']){
            $emoji = $reaction->emoji->name;
            $member = $reaction->member;
            echo "Reaction Added from Notify Message. Updating Assigned User Role. \n";
            echo "Emoji: $emoji\n"; // Debug: Print emoji name
            echo "User ID: " . $member->username . "\n"; // Debug: Print user ID

            // New Leads Role
            if ($emoji === '🤝') {
                // Assign a role to the user who reacted with the 🤝 emoji
                if ($member instanceof Member) {
                    $roleId = $_ENV['NEW_LEADS'];
                    $member->addRole($roleId);
                    echo "Assigned role to user: {$member->username}", PHP_EOL;

                    SendBotLog(
                        $discord, 
                        'ROLE', 
                        'Role assigned to: ' . $member->username,
                    );
                }
            }

            // New Leads Role
            if ($emoji === '⛔') {
                // Assign a role to the user who reacted with the 🤝 emoji
                if ($member instanceof Member) {
                    $roleId = $_ENV['SESSION_CANCELLED'];
                    $member->addRole($roleId);
                    echo "Assigned role to user: {$member->username}", PHP_EOL;

                    SendBotLog(
                        $discord, 
                        'ROLE', 
                        'Role assigned to: ' . $member->username,
                    );
                }
            }

            // New Leads Role
            if ($emoji === '📓') {
                // Assign a role to the user who reacted with the 🤝 emoji
                if ($member instanceof Member) {
                    $roleId = $_ENV['USER_NOTE'];
                    $member->addRole($roleId);
                    echo "Assigned role to user: {$member->username}", PHP_EOL;

                    SendBotLog(
                        $discord, 
                        'ROLE', 
                        'Role assigned to: ' . $member->username,
                    );
                }
            }
        }

    });

$discord->on(Event::MESSAGE_REACTION_REMOVE, function (MessageReaction $reaction, Discord $discord) {
    echo "Reaction Removed. Checking Defined Rules. \n", PHP_EOL;

    if ($reaction->message_id === $_ENV['NOTIFY_MESSAGE']) {
        $emoji = $reaction->emoji->name;
        $channel = $reaction->channel;
        $guild = $channel->guild;

        echo "Reaction Removed from Notify Message. Updating Assigned User Role. \n";
        echo "Emoji: $emoji\n"; // Debug: Print emoji name
        echo "User: $reaction->user_id \n";

            $userId = $reaction->user_id;
            
            // Fetch the member object based on the user ID
            $member = $guild->members->get('id', $userId);

            echo "User D: $member";

            if ($member) {
                echo "User ID: " . $member->username . "\n"; // Debug: Print user ID

                // Your role removal logic here based on the emoji
                if ($emoji === '🤝') {
                    echo "New Leads";
                    // Assign a role to the user who reacted with the 🤝 emoji
                    $roleId = $_ENV['NEW_LEADS'];
                    
                    try {
                        $member->removeRole($roleId);
                        echo "Removed Role for user: {$member->username}", PHP_EOL;
                    } catch (Exception $e) {
                        echo "Error removing role: " . $e->getMessage() . "\n";
                    }
                }
            } else {
                echo "Member not found for user ID: $userId\n";
            }
    }
});

    
    
    
    
    