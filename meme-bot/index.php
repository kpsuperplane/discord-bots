<?php

/*
 * This file is apart of the DiscordPHP project.
 *
 * Copyright (c) 2016 David Cole <david@team-reflex.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

require ('lib/pepify.php');

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\WebSockets\WebSocket;

// Includes the Composer autoload file
include __DIR__.'/vendor/autoload.php';

// Init the Discord instance.
$discord = new Discord(['token' => 'MTkwNjYwMDk3OTgxMTUzMjgy.Cju9jQ.AIke3N9bsVxJ3TYrtXhCB44ShQM']);
// Init the WebSocket instance.
$ws = new WebSocket($discord);

// We use EventEmitters to emit events. They are pretty much
// identical to the JavaScript/NodeJS implementation.
//
// Here we are waiting for the WebSocket client to parse the READY frame. Once
// it has done that it will run the code in the closure.
$ws->on(
    'ready',
    function ($discord) use ($ws) {
        // In here we can access any of the WebSocket events.
        //
        // There is a list of event constants that you can
        // find here: https://teamreflex.github.io/DiscordPHP/classes/Discord.WebSockets.Event.html
        //
        // We will echo to the console that the WebSocket is ready.
        echo 'Discord WebSocket is ready!'.PHP_EOL;

        // Here we will just log all messages.
        $ws->on(
            Event::MESSAGE_CREATE,
            function ($message, $discord, $newdiscord) {
                // We are just checking if the message equils to ping and replying to the user with a pong!
                if (fnmatch("!mem*", $message -> content)) {
                    $response = "";
                    $command = explode(" ", trim(substr($message -> content, 6)));
                    if($command[0] == "pepe"){
                      $message->getChannelAttribute()->sendFile("assets/pepe.jpg", "pepe.jpg");
                    }else if($command[0] == "pepify"){
                      $users = $discord->getClient()->getUsersAttribute();
                      if(!isset($command[1])){
                        $message->getChannelAttribute()->sendMessage("Please specify a username or discriminator #");
                        return;
                      }
                      $username = $command[1];
                      foreach($users as $key=>$user){
                        if($user->username == $username || $user->discriminator == $username){
                          $image = $user->getAvatarAttribute();
                          if($image != null){
                            $message->getChannelAttribute()->sendMessage("@".$user->username."#".$user->discriminator);
                            $ch = curl_init($image);
                            $fp = fopen("tmp/pepify-source.jpg", 'wb');
                            curl_setopt($ch, CURLOPT_FILE, $fp);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_exec($ch);
                            curl_close($ch);
                            fclose($fp);
                            imagepng(pepify("tmp/pepify-source.jpg", "assets/pepetrans.png"), "tmp/pepify.png");
                            $message->getChannelAttribute()->sendFile("tmp/pepify.png", "pepe.png");
                            unlink("tmp/pepify.png");
                            unlink("tmp/pepify-source.jpg");
                          }else{
                            $message->getChannelAttribute()->sendMessage("User `".$command[1]."` has no profile picture.");
                          }
                          return;
                        }
                      }
                      $message->getChannelAttribute()->sendMessage("Cannot find user `".$command[1]."`.");
                    }else{
$response = 'Available Commands:
  pepe
  pepify <username/discriminator#>
';
                    }
                    if($response != ""){
                      $message->getChannelAttribute()->sendMessage($response);
                    }
                }

            }
        );
    }
);

$ws->on(
    'error',
    function ($error, $ws) {
        dump($error);
        exit(1);
    }
);

// Now we will run the ReactPHP Event Loop!
$ws->run();
