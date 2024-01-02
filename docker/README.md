## Setting up the Docker environment

Make sure everything has been installed:
```
pnpm install
```

To create and start a local development environment with the plugin locally enter this command:

```
pnpm docker:up:recreate
```

This will (re-)create all containers and run a setup script to ensure everything is configured.

Once you've created the environment, you can quickly bring it back up with `pnpm docker:up`.

## WordPress Admin
Open http://localhost:8082/wp-admin/
```
Username: admin
Password: admin
```

## Connecting to MySQL
Connect using any MySQL clients with these credentials:

```
Host: localhost
Port: 3308
Username: wordpress
Password: wordpress
```

### Changing default port for xDebug
To change the default port for xDebug you should create `docker-compose.override.yml` with the following contents:
```
version: '3'

services:
  wordpress:
    build:
      args:
        - XDEBUG_REMOTE_PORT=9003 # IDE/Editor's listener port
```
I used port `9003` as an example.
To apply the change, restart your containers using `pnpm docker:down && pnpm docker:up`
