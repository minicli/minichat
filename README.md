# Minichat for Twitch

This project implements a command-line chatbot for Twitch / IRC built with Minicli (PHP).

_Notice: this is not a regular chat client where you can reply from a prompt. You'll have to define automatic responses and behavior programmatically._

## Building Minichat
You can find a detailed tutorial covering this project here:

- [Creating a Twitch / IRC Chatbot in PHP with Minicli](https://dev.to/erikaheidi/creating-a-twitch-irc-chatbot-in-php-with-minicli-45mo)

## Installation & Setup

1. Clone this repository
2. Run `composer install`
3. Set up your Twitch username and [oauth key](https://twitchapps.com/tmi/) in the `minichat` script
4. Run `./minichat twitch` to connect to your stream chat

## TO DO

- Ignore list
- Bot commands