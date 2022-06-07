# Getting Started

## Runtime Dependencies

- php7.2+
- SphinxSearch
- NodeJS
- Ocelot
- Nginx
- Memcached
- Mysql 5.7
- Linux crontab

## Quick Start

> Set up [Docker](https://docs.docker.com/get-started/) and [Docker Compose](https://docs.docker.com/compose/install/)

```shell
# Clone repository
git clone xxx/GazellePW
cd GazellePW

# Copy and edit .env file
cp .env.template .env

# Bulild Docker
docker build -t gpw-web:latest .
# For Macbook M1
docker buildx create --use
docker buildx build --platform linux/amd64 --load -t gpw-web:latest .

# Run Docker
docker compose -p gazelle up
```

Now you can access the website through http://localhost:9000

To register: without email service configed, you could check the email file under local environment located at `./cache/emails/`. Please click on the link in the email to activate your account.

---

For more config detail, you can go to [config.template.php](classes/config.template.php). If you need the tracker, please deploy the [Ocelot](https://github.com/Mosasauroidea/Ocelot).

## Setup Editor

- VSCode: Install [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode), [XML](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-xml), [PHP Intelephense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client)

## Going further

Create a Mysql phinx migration:

```shell
 docker exec -it --user gazelle gpw-web  vendor/bin/phinx create MyNewMigration
```

Edit the resulting file and then apply it:

```shell
docker exec -it --user gazelle gpw-web vendor/bin/phinx migrate
```
