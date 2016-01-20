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
}

$(document).ready(function() {
    
    $('#soa button[type=submit]').click(function(){
        if(validateSoaData()) {
            $('#soa button[type=submit]').prop("disabled", "true");
        }
    });
    
    $('#soa form input').bind("paste keyup change", function() {
        var regex = new RegExp($(this).attr('data-regex'));
        if(!regex.test($(this).val()) && $(this).val().length > 0) {
            $(this).parent().addClass("has-error");
        } else {
            $(this).parent().removeClass("has-error"); 
        }
    });
    
    $('#searchType').select2({
        placeholder: "Filter..."
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
            .append('<td>' + item.content + '</td>')
            .append('<td>' + item.priority + '</td>')
            .append('<td>' + item.ttl + '</td>')
            .append('<td><span class="glyphicon glyphicon-pencil cursor-pointer "></span></td>')
            .append('<td><span class="glyphicon glyphicon-trash cursor-pointer "></span></td>');
       
    });
}

function requestRecordData() {
    var restrictions = {};
    
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