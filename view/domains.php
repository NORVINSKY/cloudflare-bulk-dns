<style>
    #getAllIPs{ cursor: pointer;}
</style>
<main class="container" style="padding-top: 0px!important;">
    <nav>
        <ul>

        </ul>
        <ul>
            <li><strong><?=APP_NAME?></strong></li>
        </ul>
        <ul>
            <li><a href="/" class="secondary">change ips</a></li>
            <li><a href="/?act=domains" class="secondary">add domains</a></li>
            <li><a href="/?act=logout" class="secondary">exit</a></li>
        </ul>
    </nav>
    <input type="hidden" id="addZoneStat" name="addZoneStat" value="false">
    <div id="prgrsBlock"></div>
    <div id="addZoneBlock">

            <label>
                Server IP
                <input
                        name="serverIP"
                        placeholder="Enter server IP"
                        id="servIP"
                        required
                />
            </label>

        <fieldset>
            <legend>CloudFlare DNS proxy:</legend>
            <input type="radio" id="enable_prox" name="dns_prox"  aria-invalid="false"/>
            <label for="enable_prox">Enable</label>
            <input type="radio" id="disable_prox" name="dns_prox" checked aria-invalid="true"/>
            <label for="disable_prox">Disable</label>
        </fieldset>

        <fieldset>
            <legend>CloudFlare RUM (Analytics):</legend>
            <input type="radio" id="enable_rum" name="rum_prox" aria-invalid="false"/>
            <label for="enable_rum">Enable</label>
            <input type="radio" id="disable_rum" name="rum_prox" checked aria-invalid="true"/>
            <label for="disable_rum">Disable</label>
        </fieldset>


            <label>
                Domains list
                <textarea style="margin-bottom: 0px;" id="domList" name="domlist" rows="7" required></textarea>

            </label>
        <label>
            <input style="margin-bottom: 5px;" id="plIndicator" name="plIndicator" type="checkbox" role="switch" /> Delete processed domains
        </label>
        <button id="addDoms" type="submit">ADD DOMAINS</button>
    </div>
    <table id="catTable" role="grid">
        <thead>
        <tr>

            <th data-sort="domain">Domain</th>
            <th data-sort="NS">Name Servers</th>
            <th data-sort="ips">IPs</th>
            <th data-sort="check">Status</th>

        </tr>
        </thead>
        <tbody>
        <tr><td colspan="4"><i>Loading...</i></td></tr>
        </tbody>
    </table>
    <div class="grid">
        <button id="prevButton" class="outline"><< Previous page</button>
        <button id="nextButton" class="outline">Next page >></button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', init, false);

        let data, table, sortCol;
        let sortAsc = false;
        const pageSize = 500;
        let curPage = 1;

        async function init() {

            // Select the table (well, tbody)
            table = document.querySelector('#catTable tbody');
            // get the cats
            let resp = await fetch('/index.php?getpending');
            data = await resp.json();
            renderTable();

            document.querySelector('#nextButton').addEventListener('click', nextPage, false);
            document.querySelector('#prevButton').addEventListener('click', previousPage, false);
        }

        function renderTable() {
            // create html
            let result = '';
            data.filter((row, index) => {
                let start = (curPage-1)*pageSize;
                let end =curPage*pageSize;
                if(index >= start && index < end) return true;
            }).forEach(c => {
                result += `<tr>
     <td>${c.domain}</td>
     <td>${c.NS}</td>
     <td><span id='zid_${c.id}' style="cursor: pointer;" onclick="getIP('${c.id}')"><b>GET</b></span></td>
     <td> ${c.status}</td>

     </tr>`;
            });
            table.innerHTML = result;
        }

        function previousPage() {
            if(curPage > 1) curPage--;
            renderTable();
        }

        function nextPage() {
            if((curPage * pageSize) < data.length) curPage++;
            renderTable();
        }


        //запрашиваем IP
        function getIP(zid){

            $('#zid_'+zid).html('Loading...');

            $.post( "index.php", { act: "getIP", zid: zid })
                .done(function( data ) {
                    data = JSON.parse(data);

                    if(data[0] === 'error'){
                        $('#zid_'+zid).html('Error');
                    }else{ $('#zid_'+zid).html(data[0]); }

                }).fail(function() {
                $('#zid_'+zid).html('Error');
            });
        }
        var proxyStatus = 'false';
        $('input[name="dns_prox"]').change(function() {
            proxyStatus = $('#enable_prox').is(':checked').toString();
        });

        var rumStatus = 'false';
        $('input[name="rum_prox"]').change(function() {
            rumStatus = $('#enable_rum').is(':checked').toString();
        });


        //добавляем
        $( "#addDoms" ).on( "click", async function() {

            const plIndicator = $('#plIndicator').is(':checked');

            //принимаем и проверяем ip
            let servIP = $.trim($('#servIP').val());
            if (validateIP(servIP)) {

                //принимаем, чистим и проверяем список доменов
                let c_domains = extractUniqueDomains();
                let ufCount = c_domains.length;
                if(ufCount > 0){

                    $('#addZoneBlock').hide();
                    $('#prgrsBlock').html(`Adding domains (<span id="uCount">0</span>/<span id="ufCount">${ufCount}</span>)<progress id="prgrsBar" value="0" max="${ufCount}"></progress> <button id="stopUPD" onclick="stopUPD()" class="contrast">STOP</button>`);

                    $('#addZoneStat').val('true')

                    //делаем запросы
                    for(let i=0; i<c_domains.length; i++){
                        let c_domain = c_domains[i];

                        await new Promise((resolve,reject) => {

                            if($('#addZoneStat').val() === 'false'){ reject(); return false; }

                            $.post( "index.php", { act: "addZone", domain: c_domain, servIP: servIP, dnsProx: proxyStatus, rumProx: rumStatus })
                                .done(function( data ) {
                                    data = JSON.parse(data);

                                    if(data.status === 'done' || data.status === 'warndone'){

                                        //добавляем элемент в таблицу
                                        let trHtml = `<tr>
     <td>${data.domain}</td>
     <td>${data.ns}</td>
     <td>${data.ips}</td>
     <td>${data.msg}</td>
</tr>`;
                                        $("#catTable tbody").prepend(trHtml);

                                        $('#uCount').html(i+1);
                                        $('#prgrsBar').val(i+1);

                                        if(plIndicator){ //переписываем список доменов
                                            c_domains[i] = false; //затираем текущее значение
                                            $('#domList').val(c_domains.filter(item => typeof item === 'string').join('\n'));
                                        }

                                        resolve()
                                    }else {
                                        //неизвестная ошибка, выводим ее и останавливаем цикл
                                        alert(data.msg);
                                        afterIPs();
                                        reject()
                                    }

                                }).fail(function() {
                                    afterIPs();
                                    alert('Server is down or something wrong else =(');
                                    return false;
                            });
                        })
                    }
                    afterIPs();
                }else{ alert('Please enter correct domains.'); }
            } else {
                alert('Please enter correct IP address.');
            }

        } );


        function sleep (time) {
            return new Promise((resolve) => setTimeout(resolve, time));
        }

        function validateIP(address) {
            // Регулярное выражение для проверки IP-адреса
            const regex = /^((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])$/;
            return regex.test(address);
        }

        function extractUniqueDomains() {
            // Получаем текст из textarea
            const rawDomains = $('#domList').val();

            // Регулярное выражение для поиска доменов (простое, можно усложнить)
            const domainRegex = /\b(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,6}\b/gi;

            const matches = rawDomains.match(domainRegex);

            const uniqueDomains = getUniqueElementsIgnoreCase(matches);

            return Array.from(uniqueDomains);
        }

        //останавливаем обновление
        function stopUPD(){
            $('#addZoneStat').val('false')
            afterIPs();
        }

        //возвращаем элементы после остановки сбора IP
        function afterIPs(){
            $('#addZoneBlock').show();
            $('#prgrsBlock').html(' ');

        }

        function getUniqueElementsIgnoreCase(arr) {
            const uniqueSet = new Set();
            arr.forEach(item => uniqueSet.add(item.toLowerCase()));
            return [...uniqueSet];
        }
    </script>