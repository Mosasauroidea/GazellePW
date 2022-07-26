# Getting Started

## Setup Project

1. Install [Docker](https://docs.docker.com/get-started/) and [Docker Compose](https://docs.docker.com/compose/install/)

2. Run it

```shell
# Build Docker image
# For x86 CPU
docker build -t gpw-web:latest .
# For ARM CPU (Macbook M1)
docker buildx create --use
docker buildx build --platform linux/amd64 --load -t gpw-web:latest .

# Start
docker-compose -p gazelle up
```

3. Now you can access the website through http://localhost:9000

4. Register an account: check email in `./cache/emails/` to activate your account.

5. If you need the tracker, please deploy the [Ocelot](https://github.com/Mosasauroidea/Ocelot).

## Setup Editor

- VSCode: Install [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode), [XML](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-xml), [PHP Intelephense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client), [Crowdin](https://marketplace.visualstudio.com/items?itemName=Crowdin.vscode-crowdin)

## Going further

```shell
# Create a database migration
docker-compose exec -it --user gazelle web  vendor/bin/phinx create MyNewMigration

# Run database migration
docker-compose exec -it --user gazelle web vendor/bin/phinx migrate
```
