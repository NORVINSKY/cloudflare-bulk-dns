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
    <input type="hidden" id="getIPstat" name="getIPstat" value="false">
    <input type="hidden" id="updRecStat" name="updRecStat" value="false">
    <div id="prgrsBlock"></div>
<div id="updRecordBlock">
    <label for="records">Record Type</label>
    <select id="records" required>
        <option value="false" disabled selected>Select record type</option>
        <option value="A" >A</option>
        <option value="TXT" >TXT</option>
        <option value="NS" >NS</option>
        <option value="MX" >MX</option>
        <option value="CNAME" >CNAME</option>
    </select>

    <label for="content">
        <input type="text" id="content" name="content" placeholder="Content" required>
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
        <input type="radio" id="rum_home_enable" name="cf_rum" value="enable" aria-invalid="false"/>
        <label for="rum_home_enable">Enable</label>
        <input type="radio" id="rum_home_disable" name="cf_rum" value="disable" aria-invalid="true"/>
        <label for="rum_home_disable">Disable</label>
        <input type="radio" id="rum_home_skip" name="cf_rum" value="skip" checked />
        <label for="rum_home_skip">Do not change</label>
    </fieldset>

    <button id="update" type="submit">UPDATE</button>
</div>

    <table id="catTable" role="grid">
    <thead>
    <tr>

        <th data-sort="domain">Domain</th>

        <th data-sort="NS">NS</th>
        <th data-sort="ips">IPs <span id="getAllIPs" onclick="getAllIPs()">[<b>GET ALL</b>]</span></th>
        <th data-sort="check">
            <label for="selAll">
                <input type="checkbox" id="selAll" name="terms">
            </label>
        </th>

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
        let resp = await fetch('/index.php?getzones');
        data = await resp.json();
        renderTable();

        document.querySelector('#nextButton').addEventListener('click', nextPage, false);
        document.querySelector('#prevButton').addEventListener('click', previousPage, false);
    }

    function renderTable() {
        // create html
        let result = '';
		let vi = 0;
        data.filter((row, index) => {
            let start = (curPage-1)*pageSize;
            let end =curPage*pageSize;
            if(index >= start && index < end) return true;
        }).forEach(c => {
			vi++;
            result += `<tr>
     <td id='did_${c.id}_${vi}' >${c.domain}</td>
     <td>${c.NS}</td>
     <td><span id='zid_${c.id}_${vi}' style="cursor: pointer;" onclick="getIP('${c.id}', '${vi}')"><b>GET</b></span></td>
<td data-id="${c.id}_${vi}"> <label for="switch_${c.id}_${vi}">
    <input type="checkbox" id="switch_${c.id}_${vi}" class="selctDom" data-zid="${c.id}_${vi}" name="switch" role="switch">
  </label>
</td>

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

    //отметить все
    $( "#selAll" ).on( "change", function() {

        if($('input:checkbox#selAll')[0].checked){
            $(".selctDom").map(function() {
                 if(!$(this)[0].checked){ $(this).click(); }
            }).get();
        }else {
            $(".selctDom").map(function() {
                if($(this)[0].checked){ $(this).click(); }
            }).get();
        }
    } );

    //запрашиваем IP
    function getIP(zid, dop){

        $('#zid_'+zid+'_'+dop).html('Loading...');
		
		let dom = $('#did_'+zid+'_'+dop).text();
		
        $.post( "index.php", { act: "getIP", zid: zid, dom: dom })
            .done(function( data ) {
                data = JSON.parse(data);

                if(data[0] === 'error'){
                    $('#zid_'+zid+'_'+dop).html('Error');
                }else{ $('#zid_'+zid+'_'+dop).html(data[0]); }

            }).fail(function() {
                $('#zid_'+zid+'_'+dop).html('Error');
            });
    }

    var proxyStatus = 'false';
    $('input[name="dns_prox"]').change(function() {
        proxyStatus = $('#enable_prox').is(':checked').toString();
    });
	
    var rumStatus = 'skip';
    $('input[name="cf_rum"]').change(function() {
        rumStatus = $('input[name="cf_rum"]:checked').val();
    });

    //обновляем
    $( "#update" ).on( "click", async function() {

        //проверяем выбор типа
        let type = $('#records').find(":selected").val();
        if(type === '' || type === 'false'){ alert('Please select record type.'); }else{

            let content = $('#content').val();
            content = content.replace(/\s+/g, ' ').trim();
            //проверяем наличие контента
            if(content !== '' && content !== ' '){

                let zids = [];
                //проверяем отмеченные
                $(".selctDom").map(function() {
                    if($(this)[0].checked){
                        zids.push($(this).data());
                    }
                }).get();

                let ufCount = zids.length;

                if( ufCount > 0){

                    //прячем форму
                    $('#updRecordBlock').hide();
                    $('#getAllIPs').hide();
                    $('#prgrsBlock').html(`Updating records (<span id="uCount">0</span>/<span id="ufCount">${ufCount}</span>)<progress id="prgrsBar" value="0" max="${ufCount}"></progress> <button id="stopUPD" onclick="stopUPD()" class="contrast">STOP</button>`);
                    $('#updRecStat').val('true')


                    for(let i=0; i<zids.length; i++){
                        let zid = zids[i].zid;
						
						let parts = zid.split('_');

						zid = parts[0]; 
						let dop = parts[1];
						
						let dom = $('#did_'+zid+'_'+dop).text();

                        await new Promise((resolve,reject) => {

                            if($('#updRecStat').val() === 'false'){ reject(); return false; }

                         $.post( "index.php", { act: "updZone", zid: zid, type:type, content:content, dnsProx: proxyStatus, dom: dom, rumProx: rumStatus })
                            .done(function( data ) {
                                data = JSON.parse(data);

                                if(data.status === 'done' || data.status === 'warndone'){
                                    $('#zid_'+zid+'_'+dop).html(data.msg);
                                    $('#uCount').html(i+1);
                                    $('#prgrsBar').val(i+1);
                                    resolve()
                                }else {
                                    $('#zid_'+zid+'_'+dop).html('Error');
                                    alert(data.msg);
                                    afterIPs();
                                    reject()
                                }

                            }).fail(function() {
                            $('#zid_'+zid+'_'+dop).html('Error'); //исправить на нормальный тип ID
                        });

                    })

                    }

                    afterIPs();
                    
                }else{ alert('Please select some domains.'); }

            }else{ alert('Please enter content.'); }
        }

    } );

    //получаем все IP
    function getAllIPs(){

        let zids = [];

        $(".selctDom").map(function() {
                zids.push($(this).data());
        }).get();

        let fCount = zids.length;

        $('#updRecordBlock').hide();
        $('#getAllIPs').hide();
        $('#prgrsBlock').html(`Getting IPs (<span id="cCount">0</span>/<span id="fCount">${fCount}</span>)<progress id="prgrsBar" value="0" max="${fCount}"></progress> <button id="stopIPs" onclick="stopIPs()" class="contrast">STOP</button>`);
        $('#getIPstat').val('true')


        zids.forEach((zid,i) => {
            zid = zid.zid;

			let parts = zid.split('_');

			zid = parts[0]; 
			let dop = parts[1]; 

            if (window['time'+i]) {
                clearTimeout(window['time'+i])
                window['time'+i] = null
            }

            window['time'+i]  = setTimeout(() => {

                    if($('#getIPstat').val() === 'true'){
                        getIP(zid, dop)
                        $('#cCount').html(i+1);
                        $('#prgrsBar').val(i+1);

                        if( i+1 >= fCount){ $('#getIPstat').val('false'); afterIPs(); }
                    }
                }, i * 1000);
        })

    }

    //останавливаем сбор IP
    function stopIPs(){
        $('#getIPstat').val('false')
        afterIPs()
    }

    //останавливаем обновление
    function stopUPD(){
        $('#updRecStat').val('false')
        afterIPs()
    }

    //возвращаем элементы после остановки сбора IP
    function afterIPs(){
        $('#updRecordBlock').show();
        $('#getAllIPs').show();
        $('#prgrsBlock').html(' ');

    }

    function sleep (time) {
        return new Promise((resolve) => setTimeout(resolve, time));
    }
</script>