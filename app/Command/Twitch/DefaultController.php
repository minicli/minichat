<?php

namespace App\Command\Twitch;

use App\TwitchChatClient;
use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle()
    {
        $this->getPrinter()->info("Starting Minichat for Twitch...");

        $app = $this->getApp();

        $twitch_user = $app->config->twitch_user;
        $twitch_oauth = $app->config->twitch_oauth;

        if (!$twitch_user OR !$twitch_oauth) {
            $this->getPrinter()->error("Missing 'twitch_user' and/or 'twitch_oauth' config settings.");
            return;
        }

        $client = new TwitchChatClient($twitch_user, $twitch_oauth);
        $client->connect();

        if (!$client->isConnected()) {
            $this->getPrinter()->error("It was not possible to connect.");
            return;
        }

        $this->getPrinter()->info("Connected.\n");

        while (true) {
            $content = $client->read(512);

            //is it a ping?
            if (strstr($content, 'PING')) {
                $client->send('PONG :tmi.twitch.tv');
                continue;
            }

            //is it an actual msg?
            if (strstr($content, 'PRIVMSG')) {
                $parsed = $this->parseMessage($content);
                $nick = $parsed['nick'];
                $message = $parsed['message'];
                //is there a chat command in it?
                $this->getPrinter()->info("Received: " . $message);

                if ($message[0] == '!') {
                    $this->getPrinter()->info("Command detected.");
                    //it's a command now what
                    $command = $this->parseCommand($message);
                    $this->getPrinter()->info("Found command: " . $command);


                    $this->getPrinter()->info("Sending message...");
                    $client->sendMessage($twitch_user, "Will this WORK?");

                }

                $this->printMessage($nick, $message);
                continue;
            }

            sleep(5);
        }
    }

    protected function parseMessage($raw_message)
    {
        $parts = explode(":", $raw_message, 3);
        $nick_parts = explode("!", $parts[1]);

        $nick = $nick_parts[0];
        $message = $parts[2];

        return [ 'nick' => $nick, 'message' => $message ];
    }

    protected function printMessage($nick, $message)
    {
        $style_nick = "info";

        if ($nick === $this->getApp()->config->twitch_user) {
            $style_nick = "info_alt";
        }

        $this->getPrinter()->out($nick, $style_nick);
        $this->getPrinter()->out(': ');
        $this->getPrinter()->out($message);
        $this->getPrinter()->newline();
    }

    protected function parseCommand($message)
    {
        $explode = explode(' ', $message, 2);

        return substr($explode[0], 1);
    }
}