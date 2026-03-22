<?php
date_default_timezone_set('Europe/Moscow');

#Пути
const _ROOT_ = __DIR__ . DIRECTORY_SEPARATOR; //Корень
const _LIB_ = _ROOT_ . 'lib' . DIRECTORY_SEPARATOR; //Подключаемые файлы
const _VIEW_ = _ROOT_ . 'view' . DIRECTORY_SEPARATOR; //Шаблон интерфейса

const APP_NAME = 'BULK CloudFlare';
const _UA_ = 'Mozilla/5.0 (Windows NT 10.0; rv:102.0) Gecko/20100101 Firefox/102.0'; //юзерагент
const CURL_TIMEOUT = 55; //время ожидания подключения


const _R_ = '';//Путь к сайту, без слеша на конце. Для работы из корня оставить пустым, чтоб работало из папки ввести /dir где dir имя папки

define('_HOST_',  $_SERVER["SERVER_NAME"] ); //прописать домен сайта или вставить $_SERVER["SERVER_NAME"]
const _SCHEME_ = '//';
const _URL_ = _SCHEME_ . _HOST_ . '/'._R_;