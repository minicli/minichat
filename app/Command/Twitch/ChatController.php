<?php

namespace App\Command\Twitch;

use App\TwitchChatClient;
use Minicli\Command\CommandController;

class ChatController extends CommandController
{
    public function handle()
    {
        $this->getPrinter()->info("Starting Minichat...");

        $app = $this->getApp();

        $twitch_user = $app->config->twitch_user;
        $twitch_oauth = $app->config->twitch_oauth;

        if (!$twitch_user OR !$twitch_oauth) {
            $this->getPrinter()->error("Missing 'twitch_user' and/or 'twitch_oauth' config settings.");
            return;
        }

        $debug_enabled = $app->config->debug;
        $client = new TwitchChatClient($twitch_user, $twitch_oauth);

        if (!$client->isConnected()) {
            $this->getPrinter()->error("It was not possible to connect.");
            return;
        }

        $this->getPrinter()->info("Connected.\n");

        while (true) {
            $content = $client->read(512);

            if ($this->isPing($content)) {
                $client->pong();
                if ($debug_enabled) {
                    $this->getPrinter()->out("Pong sent.\n", "dim");
                }
                continue;
            }

            if ($this->isMessage($content)) {
                $this->printMessage($content);
                continue;
            }

            if ($debug_enabled) {
                $this->getPrinter()->out($content . "\n", "dim");
            }

            sleep(5);
        }
    }

    public function isMessage($content)
    {
        return strstr($content, 'PRIVMSG');
    }

        public function isPing($content)
    {
        return strstr($content, 'PING');
    }

        public function printMessage($raw_message)
    {
        $parts = explode(":", $raw_message, 3);
        $nick_parts = explode("!", $parts[1]);

        $nick = $nick_parts[0];
        $message = $parts[2];

        $ignore_list = $this->getApp()->config->twitch_chat_ignore;

        if (!is_null($ignore_list) AND in_array($nick, $ignore_list)) {
            return;
        }

        $style_nick = "info_alt";

        if ($nick === $this->getApp()->config->twitch_user) {
            $style_nick = "chat_owner";
        }

        $this->getPrinter()->out($nick, $style_nick);
        $this->getPrinter()->out(': ');
        $this->getPrinter()->out($message);
        $this->getPrinter()->newline();
    }
}