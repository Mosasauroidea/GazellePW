# Como Começar

## Instalar Projeto

1. Instale [Docker](https://docs.docker.com/get-started/) e [Docker Compose](https://docs.docker.com/compose/install/)

2. Execute-o

```shell
git clone https://github.com/Mosasauroidea/GazellePW.git
cd GazellePW

# Iniciar container
docker-compose -p gazelle up
```

3. Agora você pode acessar o site através de http://localhost:9000

4. Ao Registrar uma conta: verifique o e-mail em `./cache/emails/` para ativar sua conta.

5. Se você precisar do rastreador, por favor, implemente o [Ocelot](https://github.com/Mosasauroidea/Ocelot).

## Configurar Editor

- VSCode: Install [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode), [XML](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-xml), [PHP Intelephense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client), [Crowdin](https://marketplace.visualstudio.com/items?itemName=Crowdin.vscode-crowdin)

## Indo mais além

```shell
# Criar uma migração de banco de dados
docker-compose exec -it --user gazelle web vendor/bin/phinx create MyNewMigration

# Executar migração de banco de dados
docker-compose exec -it --user gazelle web vendor/bin/phinx migrate
```
