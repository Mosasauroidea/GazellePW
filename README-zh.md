[截图](https://raw.githubusercontent.com/Mosasauroidea/GazellePW/main/public/static/stylespreview/zh-github-dark.png)

[帮助我们翻译此项目！](./docs/zh-Hans/i18n.md)

# GazellePW

[![Crowdin](https://badges.crowdin.net/gazellepw/localized.svg)](https://crowdin.com/project/gazellepw)

全称 GazellePosterWall，一个 PT（Private Tracker）Web 框架。 Gazelle 的 **影视版本**。

## 背景

[WhatCD/Gazelle](https://github.com/WhatCD/Gazelle) 最初诞生于音乐站点。 尽管后来 OPSnet 开发组在其基础上做了一些代码重构，也只是为其音乐内容锦上添花。 而 Gazelle 的应用不止于此，我们基于 [OPSnet/Gazelle](https://github.com/OPSnet/Gazelle) 的某个版本，进行了大量的功能新增和逻辑优化。 使 Gazelle 适用于电影站的建设，我们称其为 GazellePosterWall。 而如果想要基于 GazellePW 搭建 TV 甚至是其他类别的站点，相较原版 Gazelle，也会更加容易。

## 特性

- 精美的界面
  - 响应式布局
  - 手机端界面适配
  - BBCode 工具栏
  - SVG 图标
  - 交互式图表
- 主题
  - 自动明/暗色主题切换
  - 快速创建新主题（一小时）
  - 基于组件的样式
- 影视优化
  - 发布时自动获取影片信息
  - 截图对比图(支持像素对比和曲线滤镜)
  - MediaInfo
  - 海报墙
  - 多版本
  - 种子槽位系统
- 本地化
  - 多语言（英语，中文等）
  - mdx/yaml 文件格式
  - 使用云服务（Crowdin）进行翻译
  - 支持双语内容
- 图床：本地或者 [Minio](https://github.com/minio/minio)
- 邮件发送：SMTP 或者 [Mailgun](https://www.mailgun.com/)
- 在免费和中性基础上，额外增加 25%，50%，75% 种子免费
- 现代化开发：Docker, Vite, React
- ...

## 文档

- [快速开始](./docs/zh-Hans/Getting-Started.md)
- [前端开发指南](./docs/zh-Hans/Frontend-Development-Guide.md)
- [书写 Markdown/MDX](./docs/zh-Hans/Writing-Markdown-Mdx.md)

## 参与贡献

我们非常欢迎来自社区的各种贡献！

- 翻译文档和网站
- 通过 [Issues](https://github.com/Mosasauroidea/GazellePW/issues/new/choose) 报告 bug 或者提出功能需求
- 通过 [Pull requests](https://github.com/Mosasauroidea/GazellePW/pulls) 提交代码修改

## 特别鸣谢

- 所有开发人员和贡献者
- [TheMovieDB](https://www.themoviedb.org/)
- [OMDb API](https://www.omdbapi.com/)
- [imdbphp](https://github.com/tboothman/imdbphp)
- [WhatCD/Gazelle](https://github.com/WhatCD/Gazelle)
- [OPSnet/Gazelle](https://github.com/OPSnet/Gazelle)
- [EasyCompare](https://github.com/N3xusHD/EasyCompare)
