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
    $('#table-domains>tbody').empty();
    
    $.each(data, function(index,item) {
       $('<tr></tr>').appendTo('#table-domains>tbody')
            .append('<td>' + item.id + '</td>')
            .append('<td>' + item.name + '</td>')
            .append('<td>' + item.type + '</td>')
            .append('<td>' + item.records + '</td>');
       
    });
    
    $('#table-domains>tbody>tr').click(function() {
        var id = $(this).children('td').first().text();
        var type = $(this).children('td').eq(2).text();
        
        if(type == 'MASTER') {
            location.assign('edit-master.php#' + id);
        }
    });
}