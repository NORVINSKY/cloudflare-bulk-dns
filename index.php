<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require _LIB_ . 'init.php';


//разлогин
if (isset($_GET['act']) && $_GET['act'] == 'logout') {
    $_SESSION = [];
    header('Location: /');
    die();
}


if ($_POST) {
    //авторизация
    if (isset($_POST['auth']) && $_POST['auth'] == 'true') {

        if (isset($_POST['email'])) {
            $aMail = cleanStr($_POST['email']);
        }
        if (isset($_POST['apikey'])) {
            $aKey = cleanStr($_POST['apikey']);
        }

        if (isset($aMail) && isset($aKey)) {
            $ret = CFAuth($aMail, $aKey);

            try {
                $userID = $ret['user']->getUserID();
            } catch (Exception $e) {
                $_LOG[] = $e->getMessage();
            }

            if (isset($userID) && is_string($userID)) {
                $_SESSION['auth'] = true;
                $_SESSION['aMail'] = $aMail;
                $_SESSION['aKey'] = $aKey;
            }

        } else {
            $_LOG[] = 'Please check CloudFlare API data.';
        }
    }

    //добавляем зону
    if (isset($_POST['act']) && $_POST['act'] == 'addZone') {

        $c_domain = mb_strtolower($_POST['domain']);
        $ret = CFAuth($_SESSION['aMail'], $_SESSION['aKey']);

        try {
            $EndZ = new Cloudflare\API\Endpoints\Zones($ret['adapter']);
            $EndD = new Cloudflare\API\Endpoints\DNS($ret['adapter']);
            $EndS = new Cloudflare\API\Endpoints\SSL($ret['adapter']);
        } catch (Exception $e) {
            die(json_encode(['status' => 'error', 'msg' => $e->getMessage()]));
        }

        //проверяем есть ли сабдомен
        $res_p = '';
        try {
            $parser = new DomainParser(_LIB_ . 'suffixes.php');
            $res_p = $parser->parse($c_domain);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }


        //проверяем, есть ли корневой домен
        try {
            $domRes = $EndZ->listZones($res_p['domain']);
            if (isset($domRes->result[0])) {
                $domRes = $domRes->result[0];
                $willRoot = true;
            } else {
                $willRoot = false;
                $domRes = false;
            }

        } catch (Exception $e) {
            $erMsg = $e->getMessage();
            die(json_encode(['status' => 'error', 'msg' => $erMsg]));
        }


        //пробуем добавить домен
        if (!$willRoot) {
            try {
                $domRes = $EndZ->addZone($res_p['domain'], true);
            } catch (Exception $e) {
                $erMsg = $e->getMessage();

                if (issetStr($erMsg, 'already exists')) {

                    //if(!is_string($res_p['subdomain'])){
                    die(json_encode(['status' => 'warndone', 'domain' => $res_p['domain'], 'msg' => '<b style="color:darkred;">DUPLICATE</b>', 'ns' => 'NAN', 'zid' => uniqid(), 'ips' => 'NAN']));
                    //}

                } else {
                    die(json_encode(['status' => 'error', 'msg' => $erMsg]));
                }
            }
        }


        $nameServers = implode(',', $domRes->name_servers);

        #устанавливаем ip
        $zoneRecs = $EndD->listRecords($domRes->id); //вынимаем записи зоны если они есть

        if (count($zoneRecs->result) > 1) { //если есть записи, ищем и удаляем A записи
            foreach ($zoneRecs->result as $znRec) {
                if (
                    ($znRec->type == 'A' && $znRec->name == $res_p['domain']) ||
                    ($znRec->type == 'A' && $znRec->name == 'www.' . $res_p['domain'])
                ) {

                    if (!$willRoot) {
                        $EndD->deleteRecord($domRes->id, $znRec->id);
                    }
                }

                if ($znRec->type == 'A' && $znRec->name == $res_p['subdomain'] . '.' . $res_p['domain']) {
                    //возможно сделать опцию, чтоб можно было дублю обновить ip по желанию
                    //$EndD->deleteRecord($domRes->id, $znRec->id);
                    die(json_encode(['status' => 'warndone', 'domain' => $c_domain, 'msg' => '<b style="color:darkred;">DUPLICATE</b>', 'ns' => 'NAN', 'zid' => uniqid(), 'ips' => 'NAN']));
                }
            }
        }

        //пытаемся добавить записи
        if (!$willRoot) {
            try {

                if ($_POST['dnsProx'] == 'true') {
                    $EndD->addRecord($domRes->id, "A", "@", $_POST['servIP']);
                } else {
                    $EndD->addRecord($domRes->id, "A", "@", $_POST['servIP'], 0, false);
                }

            } catch (Exception $e) {
                die(json_encode(['status' => 'error', 'msg' => 'Can`t add DNS record [@]']));
            }

            try {

                if ($_POST['dnsProx'] == 'true') {
                    $EndD->addRecord($domRes->id, "A", "www", $_POST['servIP']);
                } else {
                    $EndD->addRecord($domRes->id, "A", "www", $_POST['servIP'], 0, false);
                }

            } catch (Exception $e) {
                die(json_encode(['status' => 'error', 'msg' => 'Can`t add DNS record [www]']));
            }
        }

        //добавляем сабдомен
        if (is_string($res_p['subdomain'])) {
            try {

                if ($_POST['dnsProx'] == 'true') {
                    $EndD->addRecord($domRes->id, "A", $res_p['subdomain'], $_POST['servIP']);
                } else {
                    $EndD->addRecord($domRes->id, "A", $res_p['subdomain'], $_POST['servIP'], 0, false);
                }

            } catch (Exception $e) {
                die(json_encode(['status' => 'error', 'msg' => 'Can`t add DNS record [' . $res_p['subdomain'] . ']']));
            }
        }

        if (!$willRoot) {
            //SSL в режим flexible
            $EndS->updateSSLSetting($domRes->id, 'flexible');
            cfPATCH(['value' => 'on'], $domRes->id, 'always_use_https');

            //Управляем статусом RUM
            $rumAction = (isset($_POST['rumProx']) && $_POST['rumProx'] === 'true') ? 'enable' : 'disable';
            if (isset($domRes->account->id)) {
                $rumHashKey = 'rum_toggled_' . $domRes->id;
                cfRumToggle($domRes->account->id, $domRes->id, $res_p['domain'], $rumAction);
            }
        }

        //
        if (!$willRoot && is_string($res_p['subdomain'])) {// в логе указываем на то, что вместо с сабом был добавлен и корневой домен
            die(json_encode(['status' => 'done', 'msg' => $domRes->status, 'domain' => '<b>' . $res_p['domain'] . '</b>+' . $c_domain, 'ns' => $nameServers, 'zid' => $domRes->id, 'ips' => $_POST['servIP']]));
        }

        //если нет сабдомена и корневой домен есть - пишем дубликат
        if (!is_string($res_p['subdomain']) && $willRoot) {
            die(json_encode(['status' => 'warndone', 'domain' => $res_p['domain'], 'msg' => '<b style="color:darkgreen;">EXISTS</b>', 'ns' => $nameServers, 'zid' => uniqid(), 'ips' => $_POST['servIP']]));
        } else {
            die(json_encode(['status' => 'done', 'msg' => $domRes->status, 'domain' => $c_domain, 'ns' => $nameServers, 'zid' => $domRes->id, 'ips' => $_POST['servIP']]));
        }

        //сделать опцию, при которой из списка доменов будут удаляться обработанные

    }

    //обновляем зону
    if (isset($_POST['act']) && $_POST['act'] == 'updZone') {

        $doner = [];
        $content = cleanStr($_POST['content']);
        $dom = cleanStr($_POST['dom']);

        $ret = CFAuth($_SESSION['aMail'], $_SESSION['aKey']);
        $EDNS = new Cloudflare\API\Endpoints\DNS($ret['adapter']);

        //получаем recordID
        $zone = $EDNS->listRecords($_POST['zid']);

        $proxied = ($_POST['dnsProx'] == 'true') ? true : false;

        foreach ($zone->result as $res) {

            if ($res->type == $_POST['type'] && ($res->name == $dom || $res->name == 'www.' . $dom)) {

                try {
                    $resp = $EDNS->updateRecordDetails($_POST['zid'], $res->id, [
                        'type' => $res->type,
                        'name' => $res->name,
                        'content' => $content,
                        'ttl' => 1,
                        'proxied' => $proxied
                    ]);
                } catch (Exception $e) {
                    die(json_encode(['status' => 'error', 'msg' => $e->getMessage()]));
                }

                if (isset($resp->result->content) && !empty($resp->result->content)) {
                    $doner[] = $resp->result->content;
                }

            }
        }

        // --- RUM Logic Start --- //
        $rumAction = isset($_POST['rumProx']) ? cleanStr($_POST['rumProx']) : 'skip';
        if ($rumAction === 'enable' || $rumAction === 'disable') {
            try {
                $parser = new DomainParser(_LIB_ . 'suffixes.php');
                $res_p = $parser->parse($dom);
            } catch (Exception $e) {
            }

            // Если парсер отработал и subdomain == null, значит это корневой домен
            if (isset($res_p) && !is_string($res_p['subdomain'])) {
                try {
                    $EndZ = new Cloudflare\API\Endpoints\Zones($ret['adapter']);
                    $domRes = $EndZ->listZones($res_p['domain']);
                    if (isset($domRes->result[0])) {
                        $accountId = $domRes->result[0]->account->id;
                        cfRumToggle($accountId, $_POST['zid'], $res_p['domain'], $rumAction);
                    }
                } catch (Exception $e) {
                }
            }
        }
        // --- RUM Logic End --- //

        $doner = array_unique($doner);
        if (count($doner) === 1 && $doner[0] === $content) {
            die(json_encode(['status' => 'done', 'msg' => 'UPD: ' . $content]));
        } else {
            die(json_encode(['status' => 'warndone', 'msg' => 'UPD?: ' . $content]));
        }

    }

    //получаем единичный IP
    if (isset($_POST['act']) && $_POST['act'] == 'getIP') {

        $rawIPs = [];
        $dom = cleanStr($_POST['dom']);
        $ret = CFAuth($_SESSION['aMail'], $_SESSION['aKey']);
        $EDNS = new Cloudflare\API\Endpoints\DNS($ret['adapter']);

        $zone = $EDNS->listRecords($_POST['zid']);

        foreach ($zone->result as $res) {

            if ($res->type == 'A' && $res->name == $dom) {
                $rawIPs[] = $res->content;
            }
        }

        $rawIPs = array_unique($rawIPs);
        if (count($rawIPs) === 0) {
            $rawIPs = ['error'];
        }
        die(json_encode($rawIPs));
    }

}

#нутряк обработка
if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {

    $zonesCMP = [];

    if (isset($_GET['getpending'])) { //выгружаем pending домены

        //получаем информацию о записях
        foreach (getZones('pending') as $zn) {

            $tmpz['id'] = $zn->id;
            $tmpz['domain'] = $zn->name;
            $tmpz['NS'] = $zn->name_servers;
            $tmpz['status'] = $zn->status;

            $zonesCMP[] = $tmpz;
        }
        die(json_encode($zonesCMP));
    }

    if (isset($_GET['getzones'])) { //выгружаем активные зоны для смены ip

        //получаем информацию о записях
        foreach (getZones('active') as $zn) {

            // $tmpz['id'] = $zn->id;
            // $tmpz['domain'] = $zn->name;
            // $tmpz['NS'] = $zn->name_servers;

            // $zonesCMP[] = $tmpz;

            //если есть DNS записи выводим из как сабы
            $subZones = getSubZones($zn->id);

            if (count($subZones->result) > 1) {

                foreach ($subZones->result as $res) {
                    if (issetStr($res->name, 'www.')) {
                        continue;
                    }
                    $tmpz['id'] = $zn->id;
                    $tmpz['domain'] = $res->name;
                    $tmpz['NS'] = $zn->name_servers;

                    $zonesCMP[] = $tmpz;
                }
            }
        }

        die(json_encode($zonesCMP));
    }
}

require(_VIEW_ . 'header.php');

if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) { //нутряк

    if (isset($_GET['act']) && $_GET['act'] == 'domains') {
        require(_VIEW_ . 'domains.php');
    } else {
        require(_VIEW_ . 'home.php');
    }

} else {
    require(_VIEW_ . 'auth.php');
} //авторизация

require(_VIEW_ . 'footer.php');

