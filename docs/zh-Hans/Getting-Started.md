# 快速开始

## 配置项目

1. 安装 [Docker](https://docs.docker.com/get-started/) 和 [Docker Compose](https://docs.docker.com/compose/install/)

2. 运行

```shell
# 克隆项目
git clone https://github.com/Mosasauroidea/GazellePW.git
cd GazellePW

# 运行
docker-compose -p gazelle up
```

3. 现在你可以通过 http://localhost:9000 访问网站。

4. 注册用户: 可以通过 `./cache/emails` 查找本地邮件来激活账号。

5. 配置（可选）：创建 `config.local.php` 文件, 你可以覆盖 `config.default.php` 里面的所有配置。

6. 如果你需要 Tracker, 部署[Ocelot](https://github.com/Mosasauroidea/Ocelot)。

## 配置编辑器

- VSCode: 安装 [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode), [XML](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-xml), [PHP Intelephense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client), [Crowdin](https://marketplace.visualstudio.com/items?itemName=Crowdin.vscode-crowdin)

## 高级

```shell
# 创建数据库 migration
docker-compose exec -it --user gazelle web  vendor/bin/phinx create MyNewMigration

# 运行数据库 migration
docker-compose exec -it --user gazelle web vendor/bin/phinx migrate
```
