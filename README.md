# Basic Whatsapp bot agent

A basic structure for a whatsapp bot conversation

## Setup

`cp .env.example .env` populate `.env`

`php artisan key:generate`

`composer i`

`cp storage\app\private\responses.json.example storage\app\private\responses.json` populate `storage\app\private\responses.json`

## Local development running ngrok

`php artisan ngrok [localUrl] --extra='--url=[subdomain].ngrok-free.app'`

localUrl = url used locally to access the project

subdomain = your ngrok subdomain

## Bot responses

the bot responses will be on `storage\app\private\responses.json`

"message" - what the bot will send the user

"next" - what messaged to send next (if next == "end" the conversation ends)

for a multiple choice input remove "message" key from a response, the next user input will be checked if it matches one of the possible options/keys

the interactive (multiple choice) message buttons have a very limited character limit that will throw a error if reached

## Logs

log can be found on `storage\logs\laravel.log`

# Clearing cache

Run these commands to cleanup configurations cache

```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

Run this to clear conversation cache

```bash
php artisan optimize:clear
```

## Base message flow

```

received | user messages - user messages have contacts[0]["wa_id"]
    old messages - ignore
    new messages - respond
    interactive message replies - respond - user interactive replies have a different json structure

sent
    bot messages - ignore
    human agent messages - stop bot from responding if a human agent takes over the chat

```

## Example messages payloads

json payloads responses to messages sent and received

https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks/payload-examples
