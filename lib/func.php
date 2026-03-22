<?php

#Чистим строку от всякого
function cleanStr($str, $st = 0)
{
    if ($st == 1) {
        $str = strip_tags($str);
    }
    $str = str_replace("\n", "", $str);
    $str = str_replace("\r", "", $str);
    $str = preg_replace("/\t/", " ", "$str");
    $str = preg_replace('/[ ]+/', ' ', $str);
    $str = trim($str);
    return $str;
}

#Делаем первую букву заглавной
function mb_ucfirst($string, $enc = 'UTF-8')
{
    return mb_strtoupper(mb_substr($string, 0, 1, $enc), $enc) .
        mb_substr($string, 1, mb_strlen($string, $enc), $enc);
}

#Capitalize
function mb_ucwords($str)
{
    $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
    return ($str);
}

#Чистим массив от всякого
function cleanArray($a, $m = ['', ' '])
{
    return array_diff($a, $m);
}

#Строка в ID
function str2id($s, $sp = '-')
{
    return $st = preg_replace("/[^a-zA-Z0-9]/", $sp, cleanStr($s));
}

#Модный var_dump
function xx($v)
{
    echo '<pre>';
    die(var_dump($v));
}

//рекурсивное удаление папки
function delTree($dir)
{
    if (file_exists($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

#Цапля
function getCurl($lnk, $cookie = '', $head = [])
{

    //Скачиваем страничку
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $lnk);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'http://google.com');
    if (count($head) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    }
    //if(!empty($cookie)){ curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: {$cookie}")); }
    //curl_setopt ($ch, CURLOPT_COOKIEJAR, _DATA_.'cookies.txt');
    //curl_setopt ($ch, CURLOPT_COOKIEFILE, _DATA_.'cookies.txt');
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}



function multisort($array, $index)
{
    $new_arr = [];
    $result = [];

    foreach ($array as $k => $v) {
        $new_arr[$k] = $v[$index];
    }

    asort($new_arr);
    $keys = array_keys($new_arr);

    foreach ($new_arr as $k => $v) {
        $result[$k] = $array[$k];
    }
    return $result;
}

//Перемешиваем ассоциативный массив - php.net
function shuffle_assoc($array)
{
    $keys = array_keys($array);
    shuffle($keys);
    return array_merge(array_flip($keys), $array);
}

#Получаем IP посетителя
function getUserIP()
{
    $array = array('HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR', 'HTTP_X_REMOTECLIENT_IP');
    foreach ($array as $key)
        if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP))
            return $_SERVER[$key];
    return false;
}

//генерация случайного слова
function randWord($len = 5)
{
    $newWord = '';
    $glas = ["a", "e", "i", "y", "o", "u"];
    $soglas = ["b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "q", "r", "s", "t", "v", "x", "w", "z"];

    for ($i = 0; $i < $len / 2; $i++) {
        $ng = rand(0, count($glas) - 1);
        $nsg = rand(0, count($soglas) - 1);
        $newWord .= $glas[$ng] . $soglas[$nsg];
    }
    return $newWord;
}


//проверка авторизации
function isAuth()
{
    return (isset($_SESSION['auth'])
        && $_SESSION['auth'] === true) ? true : false;
}


//проверка наличия строки в списке $list - список, можно многомерный [0][name], $obj - объект поиска, $k - ключ по которому искать в многомерном массиве
function checkInList(array $list, $obj, $k = '')
{
    foreach ($list as $rw) {
        if (is_array($rw) && isset($rw[$k])) {
            $rw = $rw[$k];
        }
        if ($rw == $obj) {
            return true;
        }
    }
    return false;
}


//лёгкое логирование
function ERRO_LOG($log, $e = 0)
{
    if ($e == 1) {
        echo "<pre>{$log}</pre>\r\n";
    }
    file_put_contents(_DATA_ . 'error.log', date("Y-m-d H:i:s") . '#$#' . $log . "\r\n", FILE_APPEND);
}

//чтение лог файлов (если $t === 0 грузим весь файл лога)
function readLogs($p, $t = LOGSTR)
{
    $ARR = [];

    if ($t === 0) {
        if (file_exists($p)) {
            return array_reverse(file($p));
        }
    } else {
        $f = new FileReader($p);
        $c = 0;
        $t_string = '';
        while ($f->FeofBack()) {

            $t_string .= $f->ReadBack();
            if (strstr($f->ReadBack(), "\n")) {
                $ARR[$c] = $t_string;
                $c++;
                $t_string = '';
                if ($c >= $t) {
                    break;
                }
            }
        }
        $f->Close();
        return cleanArray($ARR, ["\r", "\n", "\r\n", '', ' ']);
    }
}

//сортировка многомерного массива по значению. пользоваться так: array_multisort_value($array, 'key', SORT_DESC);
function array_multisort_value()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row) {
                $tmp[$key] = $row[$field];
            }
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

//Обрезает строку до определённого количества символов не разбивая слова
function mbCutString($str, $length, $postfix = '...', $encoding = 'UTF-8')
{
    if (mb_strlen($str, $encoding) <= $length) {
        return $str;
    }

    $tmp = mb_substr($str, 0, $length, $encoding);
    return mb_substr($tmp, 0, mb_strripos($tmp, ' ', 0, $encoding), $encoding) . $postfix;
}

//ьранслитерация
function translit($str)
{
    $russian = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');

    $translit = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
    return str_replace($russian, $translit, $str);
}





//Читаем файл посимвольно с конца
class FileReader
{
    var $fp, $t;

    public function __construct($file)
    {
        $this->fp = fopen($file, 'r');
        $this->t = -2;
    }

    function Close()
    {
        if ($this->fp)
            fclose($this->fp);
        //unset($this);
    }

    function Read()
    {
        if ($this->fp) {
            $data = fgets($this->fp);
            $this->t = ftell($this->fp);
            return $data;
        }
    }

    function ReadC()
    {
        if ($this->fp) {
            $data = fgetc($this->fp);
            $this->t = ftell($this->fp);
            return $data;
        }
    }

    function ReadBack()
    {
        if ($this->fp) {
            if ($this->t == -2) {
                $stat = fstat($this->fp);
                $this->t = $stat['size'] - 1;
                unset($stat);
            }

            $data = '';
            for (; $this->t >= 0; $this->t--) {
                fseek($this->fp, $this->t);
                $c = fgetc($this->fp);
                $data = $c . $data;
                if ($c == "\n") {
                    $this->t--;
                    break;
                }
            }
            return $data;
        }
    }

    function ReadBackC()
    {
        if ($this->fp) {
            if ($this->t == -2) {
                $stat = fstat($this->fp);
                $this->t = $stat['size'] - 1;
                unset($stat);
            }

            fseek($this->fp, $this->t);
            $data = fgetc($this->fp);

            return $data;
        }
    }

    function Feof()
    {
        return feof($this->fp);
    }

    function FeofBack()
    {
        return $this->t != -1;
    }
}

//check is image?
function is_image($dir, $file)
{
    if (file_exists($dir . $file) && substr(mime_content_type($dir . $file), 0, 5) === 'image') {
        return $file;
    }
    return false;
}

//обрезка строки с сохранением слов
function textFunc($str, $maxLen)
{
    if (mb_strlen($str) > $maxLen) {
        preg_match('/^.{0,' . $maxLen . '} .*?/ui', $str, $match);
        return $match[0] . '...';
    } else {
        return $str;
    }
}


//отправка POST запроса
function curlPOST($url, $head = false, $post_data = false)
{

    if (substr($url, 0, 2) == '//') {
        $url = 'http:' . $url;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($post_data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }

    curl_setopt($ch, CURLOPT_COOKIEJAR, _STAT_ . '/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, _STAT_ . '/cookies.txt');

    if ($head) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function solveCap($url, $wsKey)
{

    $api = new RecaptchaV2Proxyless();
    $api->setVerboseMode(false);

    //your anti-captcha.com account key
    $api->setKey(AC_KEY);

    //recaptcha key from target website
    $api->setWebsiteURL($url);
    $api->setWebsiteKey($wsKey);

    if (!$api->createTask()) { //не получилось создать задачу
        sesLog($api->getErrorMessage());
        return false;
    }

    $taskId = $api->getTaskId();

    if (!$api->waitForResult()) { //не получилось решить капчу
        sesLog($api->getErrorMessage());
        return false;
    } else {
        return $api->getTaskSolution();
    }

}


//обвязочка для проверки наличия подстроки
function chkStr($r, $s)
{
    if (stripos($r, $s) !== false) {
        return true;
    }
    return false;
}

//подготовка информации о файле для Curl
function makeCurlFile($file)
{
    $mime = mime_content_type($file);
    $info = pathinfo($file);
    $name = $info['basename'];
    $output = new CURLFile($file, $mime, $name);
    return $output;
}

//логирование
function sesLog($mess)
{
    global $NW_NAME, $crID;
    $_SESSION['TASKS']['logs'][] = "<b>{$NW_NAME}#{$crID}</b>: " . $mess;
}


function sizeUnits($bytes, $dm = 2)
{

    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, $dm) . 'GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, $dm) . 'MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, $dm) . 'KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

function GetDirectorySize($path)
{
    $bytestotal = 0;
    $path = realpath($path);
    if ($path !== false && $path != '' && file_exists($path)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
            $bytestotal += $object->getSize();
        }
    }
    return $bytestotal;
}

function array_unique_key($array, $key)
{
    $tmp = $key_array = array();
    $i = 0;

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $tmp[$i] = $val;
        }
        $i++;
    }
    return $tmp;
}

//получаем байты
function parse_size($size)
{
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
    $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
    if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

//проверяет является ли строка сериализованной
function is_serialized($data, $strict = true)
{
    // If it isn't a string, it isn't serialized.
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' === $data) {
        return true;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    if ($strict) {
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
    } else {
        $semicolon = strpos($data, ';');
        $brace = strpos($data, '}');
        // Either ; or } must exist.
        if (false === $semicolon && false === $brace) {
            return false;
        }
        // But neither must be in the first X characters.
        if (false !== $semicolon && $semicolon < 3) {
            return false;
        }
        if (false !== $brace && $brace < 4) {
            return false;
        }
    }
    $token = $data[0];
    switch ($token) {
        case 's':
            if ($strict) {
                if ('"' !== substr($data, -2, 1)) {
                    return false;
                }
            } elseif (false === strpos($data, '"')) {
                return false;
            }
        // Or else fall through.
        case 'a':
        case 'O':
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b':
        case 'i':
        case 'd':
            $end = $strict ? '$' : '';
            return (bool) preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
    }
    return false;
}


//убиваем процессы по пидам
function killProcName($pids, $v = 1)
{

    if (count($pids) > $v) { //если процессов больше чем один включая текущий, прибиваем все лишние

        arsort($pids, SORT_NUMERIC); //сортируем ID процессов по убыванию
        if ($v > 0) {
            unset($pids[array_key_first($pids)]);
        }  //выпиливаем самый молодой, т.е. текущий

        foreach ($pids as $pid) { //оставшиеся процессы убиваем
            shell_exec("kill -9 {$pid}");
        }

        // !!!!!!!!! здесь позже вырубить все дочерние процессы
    }
}

//получаем PIDы
function getPids($nm)
{
    $rawPid = shell_exec("ps -ef | awk '\$NF==\"{$nm}\" {print $2}'");
    return array_map('cleanStr', cleanArray(explode("\n", $rawPid))); //разбиваем и подчищаем массив
}


//формируем ссылку
function r_Lnk($slg = '')
{
    return _URL_ . $slg;
}

//выводим отправленные данные из $_POST если они там есть
function postDT($nm)
{
    if (isset($_POST[$nm])) {
        return $_POST[$nm];
    }
}

//валидация даты в строке по формату
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

//обёртка для strpos
function issetStr($str, $substr)
{
    $result = mb_strpos($str, $substr);
    if ($result === FALSE)
        return false;
    else
        return true;
}

//генерация случайного пароля
function random_pass($length = 12)
{
    $use = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
    $api = '';
    srand((double) microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $api .= $use[rand() % strlen($use)];
    }
    return $api;
}


//конвертим значения со степенью в обычные
function exp2dec($n)
{
    if (stripos($n, 'e')) {
        $n = sprintf('%.8f', floatval($n));
    }
    return $n;
}


function getCurlOld($lnk, $post_data = false, $ref = '')
{

    global $CookSes;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_PROXYTYPE, 7);
    curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:' . $_SESSION['port']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $CookSes);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $CookSes);
    curl_setopt($ch, CURLOPT_URL, $lnk);
    if (!empty($ref)) {
        curl_setopt($ch, CURLOPT_REFERER, $ref);
    }

    if ($post_data) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    }

    return curl_redir_exec($ch);
}

function curl_redir_exec($ch)
{
    static $curl_loops = 0;
    static $curl_max_loops = 15;
    if ($curl_loops >= $curl_max_loops) {
        $curl_loops = 0;
        return FALSE;
    }
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $data = curl_exec($ch);
    list($header, $data) = explode("\r\n\r\n", $data, 2);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        $matches = array();
        preg_match('/Location:(.*?)\n/', $header, $matches);
        $url = @parse_url(trim(array_pop($matches)));
        if (!$url) {
            //couldn't process the url to redirect to
            $curl_loops = 0;
            return $data;
        }
        $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        if (!$url['scheme'])
            $url['scheme'] = $last_url['scheme'];
        if (!$url['host'])
            $url['host'] = $last_url['host'];
        if (!$url['path'])
            $url['path'] = $last_url['path'];
        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
        curl_setopt($ch, CURLOPT_URL, $new_url);
        return curl_redir_exec($ch);
        //return curl_exec($ch);

    } else {
        $curl_loops = 0;
        return $data;
    }
}



//коды ошибок для проверки регулярки
function is_preg_error()
{
    $errors = array(
        PREG_NO_ERROR => 'сode_0',
        PREG_INTERNAL_ERROR => 'Code 1 : There was an internal PCRE error',
        PREG_BACKTRACK_LIMIT_ERROR => 'Code 2 : Backtrack limit was exhausted',
        PREG_RECURSION_LIMIT_ERROR => 'Code 3 : Recursion limit was exhausted',
        PREG_BAD_UTF8_ERROR => 'Code 4 : The offset didn\'t correspond to the begin of a valid UTF-8 code point',
        PREG_BAD_UTF8_OFFSET_ERROR => 'Code 5 : Malformed UTF-8 data',
    );

    return $errors[preg_last_error()];
}

//проверка регулярки
function regCheck($reg, $dt)
{
    global $_LOG;
    @preg_match($reg, '');
    $p_e = is_preg_error();
    if ($p_e !== 'сode_0') {
        $_LOG[] = $dt . ' - ' . $p_e; //
    }
}

function CFAuth($aMail, $aKey)
{
    $ret = [];
    $key = new Cloudflare\API\Auth\APIKey($aMail, $aKey);
    $ret['adapter'] = new Cloudflare\API\Adapter\Guzzle($key);
    $ret['user'] = new Cloudflare\API\Endpoints\User($ret['adapter']);

    return $ret;
}

function cfPATCH($data, $zoneId, $settingId)
{
    $url = "https://api.cloudflare.com/client/v4/zones/$zoneId/settings/$settingId";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "X-Auth-Email: " . $_SESSION['aMail'], "X-Auth-Key: " . $_SESSION['aKey']]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function getZones($status)
{
    $zones = [];
    $ret = CFAuth($_SESSION['aMail'], $_SESSION['aKey']);
    $Ezones = new Cloudflare\API\Endpoints\Zones($ret['adapter']);

    $p = 1; //получаем список зон
    while (true) {
        $Rawzones = $Ezones->listZones('', $status, $p, 500);
        if (count($Rawzones->result) === 0) {
            break;
        }
        $zones = array_merge($zones, $Rawzones->result);
        $p++;
    }

    return $zones;
}

function getSubZones($zone)
{
    $zones = [];
    $ret = CFAuth($_SESSION['aMail'], $_SESSION['aKey']);
    $Ezones = new Cloudflare\API\Endpoints\DNS($ret['adapter']);

    $zRecs = $Ezones->listRecords($zone); //вынимаем записи зоны если они есть
    return $zRecs;
}

function cfRumToggle($accountId, $zoneId, $host, $action)
{
    if (!$accountId)
        return false;

    $headers = [
        'Content-Type: application/json',
        "X-Auth-Email: " . $_SESSION['aMail'],
        "X-Auth-Key: " . $_SESSION['aKey']
    ];

    // Вспомогательная функция для cURL
    $cf_request = function ($method, $url, $headers, $body = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    };

    // Общий шаг: ищем, есть ли уже профиль
    $page = 1;
    $siteTag = null;

    while (true) {
        $listUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/rum/site_info/list?per_page=50&page={$page}";
        $listResult = $cf_request('GET', $listUrl, $headers);

        if (!isset($listResult['success']) || !$listResult['success'] || empty($listResult['result'])) {
            break;
        }

        foreach ($listResult['result'] as $site) {
            $siteZoneTag = $site['ruleset']['zone_tag'] ?? '';
            $siteHost = $site['rules'][0]['host'] ?? '';

            if ($siteZoneTag === $zoneId || $siteHost === $host) {
                $siteTag = $site['site_tag'];
                break 2; // Нашли, выходим полностью
            }
        }

        if (isset($listResult['result_info']['total_pages']) && $page >= $listResult['result_info']['total_pages']) {
            break;
        }
        $page++;
    }

    if ($action === 'enable') {
        $payload = [
            'auto_install' => true,
            'host' => $host,
            'zone_tag' => $zoneId
        ];
        
        if ($siteTag) {
            // Профиль есть -> обновляем его
            $payload['enabled'] = true;
            $url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/rum/site_info/{$siteTag}";
            return $cf_request('PUT', $url, $headers, $payload);
        } else {
            // Профиля нет -> создаем новый
            $url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/rum/site_info";
            return $cf_request('POST', $url, $headers, $payload);
        }
        
    } elseif ($action === 'disable') {
        if ($siteTag) {
            // Профиль есть -> удаляем, как мы выяснили, это работает лучше всего
            $siteUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/rum/site_info/{$siteTag}";
            return $cf_request('DELETE', $siteUrl, $headers);
        }
        return ['success' => true, 'msg' => 'Already disabled or not found'];
    }
    
    return false;
}