# Cloudflare Bulk Domain & DNS Manager

Легковесная веб-панель на PHP для массового управления доменами (зонами), DNS-записями и веб-аналитикой (RUM) в Cloudflare. Этот инструмент создан для того, чтобы упростить процесс добавления сразу множества доменов, их первичной настройки и быстрого переключения специфичных функций Cloudflare для всего вашего аккаунта.

## Возможности

- **Массовое добавление доменов:** Добавляйте пачки корневых доменов и сабдоменов в Cloudflare в один клик.
- **Массовое обновление DNS:** Одновременное обновление IP-адресов для множества уже существующих доменов.
- **Управление DNS-прокси (Orange Cloud):** Легко включайте или отключайте проксирование трафика через Cloudflare сразу для пачки доменов.
- **Управление веб-аналитикой Cloudflare (RUM):** Массовое включение или полное отключение аналитики (Real User Monitoring). Скрипт автоматически распознает текущие конфигурации (умеет делать PUT/POST/DELETE), пропускает сабдомены для экономии лимитов API и поддерживает пагинацию аккаунтов.
- **Умная валидация:** Использует встроенный парсер доменов для того, чтобы отличать корневые домены от сабдоменов. Это помогает избежать лишних API-запросов и предотвращает создание дублирующих DNS-записей.
- **Автоматический SSL:** Автоматычно включает режим SSL `Flexible` и активирует опцию `Always Use HTTPS` для всех новых добавленных корневых доменов.

## Важные ограничения

> **⚠️ Работа с DNS:** 
> На данный момент инструмент **поддерживает работу только с `A` записями**. Управление и редактирование остальных типов (таких как `CNAME`, `TXT`, `MX`, `AAAA`) в текущей версии не поддерживается.

## Дорожная карта (Todo-list)

- [ ] Добавить поддержку `CNAME` записей.
- [ ] Добавить поддержку `TXT` записей.
- [ ] Добавить поддержку `MX` и `AAAA` записей.
- [ ] Расширить функционал DNS: разрешить полное редактирование кастомных записей и настройку TTL.
- [ ] Улучшить функционал очистки / удаления доменов из интерфейса.

## Требования

- PHP 7.4+ (или 8.x)
- Включенное расширение cURL
- [Cloudflare PHP SDK](https://github.com/cloudflare/cloudflare-php) (подключенный через Composer/папку lib)
- Валидные данные Cloudflare API: Email и Global API Key.

## Установка и использование

1. Склонируйте или скачайте репозиторий в папку вашего веб-сервера.
2. Убедитесь, что Cloudflare SDK установлен.
3. Перейдите в панель через ваш веб-браузер.
4. Авторизуйтесь, используя ваш Email от аккаунта Cloudflare и Global API Key.
5. Зайдите во вкладку "Add Domains", чтобы массово загрузить домены/сабдомены и настроить дефолтные `A` записи.
6. Выделите нужные сайты на главной странице (в таблице) для массового обновления существующих параметров.

# English

A lightweight PHP web application for bulk management of Cloudflare Domains (Zones), DNS records, and Web Analytics (RUM). This tool is designed to simplify the process of adding multiple domains, managing their initial DNS setups, and toggling Cloudflare-specific features across your account.

## Features

- **Bulk Domain Addition:** Add multiple root domains and subdomains to Cloudflare in one go.
- **Bulk DNS Updates:** Update IP addresses for multiple existing domains simultaneously.
- **DNS Proxy Control:** Easily enable or disable the Cloudflare proxy (Orange Cloud) for multiple domains.
- **Cloudflare Web Analytics (RUM) Control:** Bulk enable or disable RUM (Real User Monitoring) analytics for your Web Properties. Automatically handles existing RUM configurations, bypasses subdomains to save API limits, and supports account pagination.
- **Smart Validation:** Uses a domain parser to distinguish between root domains and subdomains to avoid redundant API calls and prevent duplicate DNS entries.
- **Automatic SSL:** Automatically sets the SSL mode to `Flexible` and enables `Always Use HTTPS` for newly added root domains.

## Important Limitations

> **⚠️ Note on DNS Records:** 
> Currently, this tool **only supports working with `A` records**. Management of other DNS record types (e.g., `CNAME`, `TXT`, `MX`, `AAAA`) is not supported in the current version.

## Todo List / Roadmap

- [ ] Add support for `CNAME` records.
- [ ] Add support for `TXT` records.
- [ ] Add support for `MX` и `AAAA` records.
- [ ] Expand DNS management to allow editing completely custom records and TTLs.
- [ ] Improve domain cleanup/deletion functionality.

## Requirements

- PHP 7.4+ (or 8.x)
- cURL extension enabled
- [Cloudflare PHP SDK](https://github.com/cloudflare/cloudflare-php) (included via Composer/lib)
- A valid Cloudflare API Email and Global API Key.

## Installation & Usage

1. Clone or download the repository to your web server environment.
2. Ensure you have the Cloudflare SDK installed.
3. Access the panel via your web browser.
4. Log in using your Cloudflare account email and Global API Key.
5. Use the "Add Domains" tab to batch import domains/subdomains and set default A records.
6. Use the main list to perform bulk updates on existing domains.
