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
    $('#zone-button-add').click(function(evt){
        evt.preventDefault();
        if(validateData()) {
            saveData(function(id) {
                location.assign("edit-master.php#" + id);
            });
        } else {
            shake($('#zone-button-add'));
        }
    });
    $('form input').bind("paste keyup change", regexValidate);
});
function validateData() {
    var error = 0;
    $('form input').change();
    $('form input').each(function() {
       if($(this).val().length <= 0 || $(this).parent().hasClass('has-error')) {
           error++;
           $(this).parent().addClass('has-error');
       } 
    });
    return error<=0;
}
function regexValidate() {
    var regex = new RegExp($(this).attr('data-regex'));
    if(!regex.test($(this).val())) {
        $(this).parent().addClass("has-error");
    } else {
        $(this).parent().removeClass("has-error"); 
    }
}
function saveData(callback) {
    var data = {
        name: $('#zone-name').val(),
        primary: $('#zone-primary').val(),
        mail: $('#zone-mail').val(),
        refresh: $('#zone-refresh').val(),
        retry: $('#zone-retry').val(),
        expire: $('#zone-expire').val(),
        ttl: $('#zone-ttl').val(),
        type: window.location.hash.substring(1),
        action: "addDomain",
        csrfToken: $('#csrfToken').text()
    };
    $.post(
        "api/add-domain.php",
        JSON.stringify(data),
        function(data) {
            callback(data.newId);
        },
        "json"
    );
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