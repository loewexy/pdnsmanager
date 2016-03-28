/* 
 * Copyright 2016 Lukas Metzger <developer@lukas-metzger.com>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

var sort = {
    field: "",
    order: 1
};

var domainName = "";

var recordTypes = [
    "A","AAAA","AFSDB","CERT","CNAME","DHCID",
    "DLV","DNSKEY","DS","EUI48","EUI64","HINFO",
    "IPSECKEY","KEY","KX","LOC","MINFO","MR",
    "MX","NAPTR","NS","NSEC","NSEC3","NSEC3PARAM",
    "OPT","PTR","RKEY","RP","RRSIG","SOA","SPF",
    "SRV","SSHFP","TLSA","TSIG","TXT","WKS"
];

$(document).ready(function() {
    
    $('#soa button[type=submit]').click(function(){
        if(validateSoaData()) {
            saveSoaData();
            $('#soa button[type=submit]').prop("disabled", true);
        } else {
            shake($('#soa button[type=submit]'));
        }
    });
    
    $('#soa input').bind("paste keyup change", function() {
        $('#soa button[type=submit]').prop("disabled", false);
    });
    
    $('#soa form input').bind("paste keyup change", regexValidate);
    $('#table-records>tfoot input').bind("paste keyup change", regexValidate);
    
    $('#searchType').select2({
        placeholder: "Filter...",
        data: recordTypes
    });
    
    $('#addType').select2({
        data: recordTypes
    });

    $('#table-records>thead>tr>td span.glyphicon').click(function() {
        var field = $(this).siblings('strong').text().toLowerCase();
        if(sort.field == field) {
            if(sort.order == 1) sort.order = 0;
            else sort.field = "";
        } else {
            sort.field = field;
            sort.order = 1;
        }
        $('#table-records>thead>tr>td span').removeClass("glyphicon-sort-by-attributes glyphicon-sort-by-attributes-alt");
       
        if(sort.field == field) {
            if(sort.order == 1) $(this).addClass("glyphicon-sort-by-attributes");
            else $(this).addClass("glyphicon-sort-by-attributes-alt");
        }
        requestRecordData();
    });
    
    $('#searchName, #searchContent').bind("paste keyup", function() {
        requestRecordData();
    });
    
    $('#searchType').change(function() {
        requestRecordData();
    });
    
    requestRecordData();
    requestSoaData();
    requestSerial();
    requestDomainName();
});

function validateSoaData() {
    
    var error = 0;
    
    $('#soa form input:not(#soa-serial)').each(function() {
       if($(this).val().length <= 0 || $(this).parent().hasClass('has-error')) {
           error++;
           $(this).parent().addClass('has-error');
       } 
    });
    
    return error<=0;
}

function recreateTable(data) {
    $('#table-records>tbody').empty();
    
    $.each(data, function(index,item) {
       $('<tr></tr>').appendTo('#table-records>tbody')
            .append('<td>' + item.id + '</td>')
            .append('<td>' + item.name + '</td>')
            .append('<td>' + item.type + '</td>')
            .append('<td class="wrap-all-words">' + item.content + '</td>')
            .append('<td>' + item.priority + '</td>')
            .append('<td>' + item.ttl + '</td>')
            .append('<td><span class="glyphicon glyphicon-pencil cursor-pointer"></span></td>')
            .append('<td><span class="glyphicon glyphicon-trash cursor-pointer"></span></td>')
            .append('<td><span class="glyphicon glyphicon-share cursor-pointer"></span></td>');
       
    });
    
    $('#table-records>tbody>tr>td>span.glyphicon-trash').click(trashClicked);
    $('#table-records>tbody>tr>td>span.glyphicon-pencil').click(editClicked);
    $('#table-records>tbody>tr>td>span.glyphicon-share').click(remoteClicked);
}

function requestRecordData() {
    var restrictions = {
        csrfToken: $('#csrfToken').text()
    };
    
    restrictions.sort = sort;
    
    var searchName = $('#searchName').val();
    if(searchName.length > 0) {
        restrictions.name = searchName;
    }
    
    var searchType = $('#searchType').val();
    if(searchType != null && searchType.length > 0) {
        restrictions.type = searchType;
    }
    
    var searchContent = $('#searchContent').val();
    if(searchContent.length > 0) {
        restrictions.content = searchContent;
    }
    
    restrictions.action = "getRecords";
    
    restrictions.domain = location.hash.substring(1);
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(restrictions),
        function(data) {
            recreateTable(data);
        },
        "json"
    );
}

function requestSoaData() {
    var data = {
        action: "getSoa",
        csrfToken: $('#csrfToken').text()
    };
    
    data.domain = location.hash.substring(1);
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(data),
        function(data) {
            $('#soa-primary').val(data.primary);
            $('#soa-mail').val(data.email);
            $('#soa-refresh').val(data.refresh);
            $('#soa-retry').val(data.retry);
            $('#soa-expire').val(data.expire);
            $('#soa-ttl').val(data.ttl);
        },
        "json"
    );
}

function requestSerial() {
    var data = {
        action: "getSerial",
        csrfToken: $('#csrfToken').text()
    };
    
    data.domain = location.hash.substring(1);
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(data),
        function(data) {
            $('#soa-serial').val(data.serial);
        },
        "json"
    );
}

function saveSoaData() {
    var data = {
        action: "saveSoa",
        csrfToken: $('#csrfToken').text()
    };
    
    data.domain = location.hash.substring(1);
    
    data.primary = $('#soa-primary').val();
    data.email = $('#soa-mail').val();
    data.refresh = $('#soa-refresh').val();
    data.retry = $('#soa-retry').val();
    data.expire = $('#soa-expire').val();
    data.ttl = $('#soa-ttl').val();
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(data),
        function() {
            requestSerial();
        },
        "json"
    );
}

function editClicked() {
    var tableCells = $(this).parent().parent().children('td');
    var tableRow = $(this).parent().parent();
    
    var valueExtractRegex = new RegExp('\.?' + domainName + "$");
    var valueName = tableCells.eq(1).text();
    valueName = valueName.replace(valueExtractRegex, "");
    tableCells.eq(1).empty();
    var inputGroupName = $('<div class="input-group"></div>').appendTo(tableCells.eq(1));
    $('<input type="text" class="form-control input-sm" data-regex="^([^.]+\.)*[^.]*$">').appendTo(inputGroupName).val(valueName);
    $('<span class="input-group-addon"></span>').appendTo(inputGroupName).text("." + domainName);
    
    var valueType = tableCells.eq(2).text();
    tableCells.eq(2).empty();
    $('<select class="form-control select-narrow-70"></select>').appendTo(tableCells.eq(2)).select2({
        data: recordTypes
    }).val(valueType).trigger("change");
   
    var valueContent = tableCells.eq(3).text();
    tableCells.eq(3).empty();
    $('<input type="text" class="form-control input-sm" data-regex="^.+$">').appendTo(tableCells.eq(3)).val(valueContent);
    
    var valuePrio = tableCells.eq(4).text();
    tableCells.eq(4).empty();
    $('<input type="text" class="form-control input-sm" size="1" data-regex="^[0-9]+$">').appendTo(tableCells.eq(4)).val(valuePrio);
    
    var valueTtl = tableCells.eq(5).text();
    tableCells.eq(5).empty();
    $('<input type="text" class="form-control input-sm" size="3" data-regex="^[0-9]+$">').appendTo(tableCells.eq(5)).val(valueTtl);
    
    tableCells.eq(6).remove();
    tableCells.eq(7).remove();
    tableCells.eq(8).remove();
    
    $(tableRow).append('<td colspan="3"><button class="btn btn-primary btn-sm">Save</button></td>');
    
    $(tableRow).find('button').click(saveRecord);
    
    enableFilter(false);
    
    $(tableRow).find("input").bind("paste keyup change", regexValidate);
}

function saveRecord() {
    
    var tableRow = $(this).parent().parent();
    
    if(!validateLine.call(this)) {
        shake($(this));
        return;
    }
    
    var data = {
        id: tableRow.children('td').eq(0).text(),
        name: tableRow.children('td').eq(1).find('input').val(),
        type: tableRow.children('td').eq(2).children('select').val(),
        content: tableRow.children('td').eq(3).children('input').val(),
        prio: tableRow.children('td').eq(4).children('input').val(),
        ttl: tableRow.children('td').eq(5).children('input').val(),
        action: "saveRecord",
        domain: location.hash.substring(1),
        csrfToken: $('#csrfToken').text()
    };
    
    if(data.name.length > 0) {
        data.name = data.name + "." + domainName;
    } else {
        data.name = domainName;
    }
    
    tableRow.children('td').eq(0).empty().text(data.id);
    tableRow.children('td').eq(1).empty().text(data.name);
    tableRow.children('td').eq(2).empty().text(data.type);
    tableRow.children('td').eq(3).empty().text(data.content);
    tableRow.children('td').eq(4).empty().text(data.prio);
    tableRow.children('td').eq(5).empty().text(data.ttl);
    
    tableRow.children('td').eq(6).remove();
    
    tableRow.append('<td><span class="glyphicon glyphicon-pencil cursor-pointer"></span></td>')
            .append('<td><span class="glyphicon glyphicon-trash cursor-pointer"></span></td>')
            .append('<td><span class="glyphicon glyphicon-share cursor-pointer"></span></td>');
    tableRow.find('span.glyphicon-trash').click(trashClicked);
    tableRow.find('span.glyphicon-pencil').click(editClicked);
    tableRow.find('span.glyphicon-share').click(remoteClicked); 
    
    enableFilter(true);
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(data),
        function() {
            requestSerial();
        },
        "json"
    );
}

function addRecord() {
    if(!validateLine.call(this)) {
        shake($('#addButton'));
        return;
    }
    
    var prio = $('#addPrio').val();
    if(prio.length === 0) prio = 0;
    
    var ttl = $('#addTtl').val();
    if(ttl.length === 0) ttl = 86400;
    
    var data = {
        type: $('#addType').val(),
        content: $('#addContent').val(),
        prio: prio,
        ttl: ttl,
        action: "addRecord",
        domain: location.hash.substring(1),
        csrfToken: $('#csrfToken').text()
    };
    
    if($('#addName').val().length > 0) {
        data.name = $('#addName').val() + "." + domainName;
    } else {
        data.name = domainName;
    }
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(data),
        function(dataRecv) {
            $('<tr></tr>').appendTo('#table-records>tbody')
                .append('<td>' + dataRecv.newId + '</td>')
                .append('<td>' + data.name + '</td>')
                .append('<td>' + data.type + '</td>')
                .append('<td class="wrap-all-words">' + data.content + '</td>')
                .append('<td>' + data.prio + '</td>')
                .append('<td>' + data.ttl + '</td>')
                .append('<td><span class="glyphicon glyphicon-pencil cursor-pointer"></span></td>')
                .append('<td><span class="glyphicon glyphicon-trash cursor-pointer"></span></td>')
                .append('<td><span class="glyphicon glyphicon-share cursor-pointer"></span></td>');
                
            $('#table-records>tbody>tr').last().find('span.glyphicon-pencil').click(editClicked);
            $('#table-records>tbody>tr').last().find('span.glyphicon-trash').click(trashClicked);
            $('#table-records>tbody>tr').last().find('span.glyphicon-share').click(remoteClicked);
            requestSerial();
            
            $('#addName').val("");
            $('#addType').val("A").change();
            $('#addContent').val("");
            $('#addPrio').val("");
            $('#addTtl').val("");
        },
        "json"
    );
}

function trashClicked() {
    var data = {
        id: $(this).parent().parent().children().eq(0).text(),
        domain: location.hash.substring(1),
        action: "removeRecord",
        csrfToken: $('#csrfToken').text()
    };
    
    var lineAffected = $(this).parent().parent();
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(data),
        function() {
            lineAffected.remove();
            requestSerial();
        },
        "json"
    );
}

function requestDomainName() {
    var data = {
        action: "getDomainName",
        domain: location.hash.substring(1),
        csrfToken: $('#csrfToken').text()
    };
    
    $.post(
        "api/edit-master.php",
        JSON.stringify(data),
        function(data) {
            $('#domain-name').text(data.name);
            $('#add-domain-name').text("." + data.name);
            domainName = data.name;
            $('#addButton').unbind().click(addRecord);
        },
        "json"
    );
}

function enableFilter(enable) {
    if(enable) {
        $('#searchName').prop("disabled", false);
        $('#searchType').prop("disabled", false);
        $('#searchContent').prop("disabled", false);
    } else {
        $('#searchName').prop("disabled", true);
        $('#searchType').prop("disabled", true);
        $('#searchContent').prop("disabled", true);
    }
}

function regexValidate() {
    var regex = new RegExp($(this).attr('data-regex'));
    if(!regex.test($(this).val())) {
        $(this).parent().addClass("has-error");
    } else {
        $(this).parent().removeClass("has-error"); 
    }
}

function validateLine() {
    $(this).parent().parent().find('input[data-regex]').change();
    var errors = 0;
    $(this).parent().parent().find('input[data-regex]').each(function() {
       if($(this).parent().hasClass('has-error')) {
           errors++;
       } 
    });
    
    return errors <= 0;
}

function remoteClicked() {
    var recordId = $(this).parent().siblings().eq(0).text();
    location.assign("edit-remote.php#" + recordId);
}

function shake(element){                                                                                                                                                                                            
    var interval = 50;                                                                                                 
    var distance = 5;                                                                                                  
    var times = 6;                                                                                                      

    $(element).css('position','relative');                                                                                  

    for(var iter=0;iter<(times+1);iter++){                                                                              
        $(element).animate({ 
            left:((iter%2===0 ? distance : distance*-1))
            },interval);                     
    }                                                                                                             

    $(element).animate({ left: 0},interval);                                                                                
}