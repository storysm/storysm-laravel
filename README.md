# Storysm Laravel

## Important Notice

ULIDs are used as the default ID type.

## Local Development

1. Download this repository and extract it anywhere in your local environment.

1. Install dependencies:

    ```
    composer install
    ```

    ```
    npm i
    ```

1. Create the `.env` file:

    ```
    cp .env.example .env
    ```

1. Assign Super User(s) by assigning `Fortify` username(s) (default to email) in `.env` file:

    ```
    AUTH_SUPER_USERS={FORTIFY_USERNAMES}
    # Separated with a comma (,)
    # Example: admin@example.com,su@example.com
    ```

1. Assign a backup email address to send backup result notifications:

    ```
    BACKUP_MAIL_TO_ADDRESS={EMAIL_ADDRESS}
    ```

1. Generate the `APP_KEY`:

    ```
    php artisan key:generate
    ```

1. Create the symbolic link for storage:

    ```
    php artisan storage:link
    ```

1. Run the migration:

    ```
    php artisan migrate
    ```

1. Run the app:

    ```
    composer run dev
    ```

To develop a universal app, follow the additional instructions below:

1. Make sure that your devices are connected to the same network.

1. Get your `IP Address`:

    On Windows:

    ```
    ipconfig /all
    ```

1. In your `.env` file, use the URL with `IP Address` instead of `localhost`. Replace `IP Address` with your own:

    ```
    APP_URL={SERVER_IP_ADRESS_URL}
    # Example: http://192.168.1.1:8000
    ```

    ```
    SANCTUM_STATEFUL_DOMAINS={CLIENT_IP_ADDRESS_URL}
    # Separated with a comma (,)
    # Example: http://192.168.1.1:8081,http://192.168.1.2:8081
    ```

    ```
    VITE_HOST={SERVER_IP_ADRESS}
    # Example: 192.168.1.1 (without http)
    ```

    ```
    SESSION_DOMAIN={SERVER_IP_ADRESS}
    # Example: 192.168.1.1 (without http)
    ```

1. Run the app:
    ```
    composer run dev:host
    ```

## LLM Commands

This app includes LLM (Language Model) commands to assist with generating commit messages and pull request messages.

To generate a commit message based on staged changes:

```
php artisan llm:commit
```

To generate a pull request message based on a commit range:

```
php artisan llm:pr
```

## Upstream

Apply any changes available from the Starter Kit Laravel [main branch](https://github.com/spektasoft/starter-kit-laravel/compare/a545523f50f944330e357895e15e9843e056ee67...main).

## License

The Storysm Laravel is open-sourced software licensed under the [MIT license](LICENSE).
