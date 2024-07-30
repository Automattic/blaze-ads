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

**Note: We recommend to change the admin user password to a more complex password, because you will open this site to the public via an HTTP tunnel.** 

## Connect the site with Jetpack

This plugin requires a valid Jetpack connection to WordPress.com, to accomplish that, you'll need a test site that can create local HTTP tunnels.
If you're an Automattician, we recommend using Jurassic Tube.

You can use the Jetpack guides as reference to start the tunnel: [Jurassic Tube](https://github.com/Automattic/jetpack/blob/trunk/docs/quick-start.md#setting-up-jurassic-tube), [ngrok](https://github.com/Automattic/jetpack/blob/trunk/tools/docker/README.md#using-ngrok-with-jetpack). 

After you configured your tunnel, you will need to enter the administration page of your site using your tunnel URL, and then connect Jetpack to WordPress.com using your WPCOM user.

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

## Troubleshooting

These are some of the errors we have had, and we want to document them in case they resurface over time.

### docker: Error response from daemon: unable to find user ...

Our setup script (`blaze-ads/bin/docker-setup.sh`) uses the wordpress:cli tool to configure the development site.
In the past, we used the user `xfs` to run the `wp cli` script, but something changed in the cli docker container, and had to update our script to start using `www-data` instead.

The main problem was probably because of WP-CLI changing its base system to Alpine, and that system uses a different user.

If this error happens in the future, check this page: https://hub.docker.com/_/wordpress#running-as-an-arbitrary-user
There may be additional information there. As an alternative, you can remove the user from the wp client call inside the `docker-setup.sh` script. 
