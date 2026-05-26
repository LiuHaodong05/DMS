
# 宿舍管理系统 (Dormitory Management System)

一个基于 PHP + MySQL 的 Web 宿舍管理系统，专为高校宿舍管理场景设计。

## 功能特性

- **管理员认证** — 基于 Session 的登录/登出验证
- **学生信息管理** — 增删改查，支持按学号/姓名/班级搜索
- **智能宿舍分配** — 自动查找空闲床位（1-4号），性别匹配校验
- **床位调度** — 换宿舍时自动释放原床位并分配新床位
- **空缺床位统计** — 实时统计空缺床位数、入住率等关键指标
- **暗色奢华 UI** — Midnight Opulence 设计风格，响应式布局

## 技术栈

- **前端**: HTML5, CSS3 (自定义设计系统), JavaScript
- **后端**: PHP 7.3+
- **数据库**: MySQL 5.7
- **开发环境**: PHPStudy / XAMPP

## 快速开始

1. 将项目放入 Web 服务器目录（如 PHPStudy 的 WWW）
2. 导入 `dorm_system.sql` 到 MySQL 数据库
3. 修改 `config.php` 中的数据库连接信息
4. 访问 `http://localhost/DMS/login.php` 登录

默认管理员：用户名 `admin`，密码 `admin`

## 项目结构

```
DMS/
├── config.php          # 数据库配置
├── login.php           # 管理员登录
├── logout.php          # 退出登录
├── index.php           # 首页（学生列表 + 数据统计）
├── add_student.php     # 添加学生/分配宿舍
├── edit_student.php    # 修改学生/调换宿舍
├── vacant_beds.php     # 空缺床位统计
├── style.css           # 全局样式
├── fill_doc.py         # 课程设计文档自动填充脚本
└── dorm_system.sql     # 数据库结构及示例数据
```

## AI 辅助开发亮点

- 使用 Claude (AI) 辅助完成 PHP 业务逻辑开发
- `fill_doc.py` — Python 脚本自动填充课程设计 Word 文档
- 暗色奢华 UI 设计系统由 AI 协助构建

## 课程设计

本项目为 **WEB 应用系统开发** 课程设计作品。
