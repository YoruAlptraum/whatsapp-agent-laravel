## dependencies

`cp .env.example .env` populate `.env`

`php artisan key:generate`

`composer i`

## running ngrok

`php artisan ngrok [localUrl] --extra='--url=[subdomain].ngrok-free.app'`

localUrl = url used locally to access the project

subdomain = your ngrok subdomain

## logs

log can be found on `storage\logs\laravel.log`
