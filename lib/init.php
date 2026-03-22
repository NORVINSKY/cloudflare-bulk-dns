<?php

require (_LIB_.'func.php');
require (_LIB_.'dom_pars.php');
require (_ROOT_.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');

$_LOG = []; //Сюда скидываем всякие не супер важные логи, которые потом выводим для наглядности
session_start();