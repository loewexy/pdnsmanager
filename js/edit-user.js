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
    $('#user-button-add').click(function(evt){
        evt.preventDefault();
        if(location.hash.substring(1) == "new") {
            addUser();
        } else {
            saveUserChanges();
        }
    });
    $('form input#user-name').bind("paste keyup change", regexValidate);
    $('#user-password').unbind().bind("paste keyup change", function() {
        $('#user-password').parent().removeClass("has-error");
    });
    $('#user-password2').unbind().bind("paste keyup change", function() {
        if($('#user-password').val() != $('#user-password2').val()) {
            $('#user-password2').parent().addClass("has-error");
        } else {
            $('#user-password2').parent().removeClass("has-error");
        }
    });
    $('#user-type').select2({
        minimumResultsForSearch: Infinity
    });
    //Prepare for new user
    if(location.hash.substring(1) == "new") {
        $('#heading').text("Add user");
        $('#user-button-add').text("Add");
        $('#user-password').attr("placeholder", "Password");
        $('#user-password2').attr("placeholder", "Password repeated");
    } else {
        getUserData();
        requestPermissions();
        $('#permissions').removeClass("defaulthidden");
    }
    $('#permissions select#selectAdd').select2({
        ajax: {
            url: "api/edit-user.php",
            dataType: "json",
            delay: 200,
            method: "post",
            data: function(params) {
                return JSON.stringify({
                    action: "searchDomains",
                    term: params.term,
                    userId: location.hash.substring(1),
                    csrfToken: $('#csrfToken').text()
                });
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            minimumInputLength: 1
        },
        placeholder: "Search...",
        minimumInputLength: 1
    });
    $('#btnAddPermissions').click(addPermissions);
});
function regexValidate() {
    var regex = new RegExp($(this).attr('data-regex'));
    if(!regex.test($(this).val())) {
        $(this).parent().addClass("has-error");
    } else {
        $(this).parent().removeClass("has-error"); 
    }
}
function addUser() {
    $('form input').change();
    if($('#user-password').val().length <= 0) {
        $('#user-password').parent().addClass("has-error");
        $('#user-password2').parent().addClass("has-error");
        shake($('#user-button-add'));
    }
    if($('#user-name').parent().hasClass("has-error")) {
        shake($('#user-button-add'));
        return;
    }
    if($('#user-password2').parent().hasClass("has-error")) {
        shake($('#user-button-add'));
        return;
    }    
    var data = {
        name: $('#user-name').val(),
        password: $('#user-password').val(),
        type: $('#user-type').val(),
        action: "addUser",
        csrfToken: $('#csrfToken').text()
    };
    $.post(
        "api/edit-user.php",
        JSON.stringify(data),
        function(data) {
            location.assign("edit-user.php#" + data.newId);
            location.reload();
        },
        "json"
    );
}
function getUserData() {
    var data = {
        id: location.hash.substring(1),
        action: "getUserData",
        csrfToken: $('#csrfToken').text()
    };
    $.post(
        "api/edit-user.php",
        JSON.stringify(data),
        function(data) {
            $('#user-name').val(data.name);
            $('#user-type').val(data.type).change();
        },
        "json"
    );
}
function saveUserChanges() {
    if($('#user-name').parent().hasClass("has-error")) {
        shake($('#user-button-add'));
        return;
    }
    if($('#user-password2').parent().hasClass("has-error")) {
        shake($('#user-button-add'));
        return;
    }
    var data = {
        id: location.hash.substring(1),
        name: $('#user-name').val(),
        type: $('#user-type').val(),
        action: "saveUserChanges",
        csrfToken: $('#csrfToken').text()
    };
    if($('#user-password').val().length > 0) {
        data.password = $('#user-password').val();
    }
    $.post(
        "api/edit-user.php",
        JSON.stringify(data),
        null,
        "json"
    );
}
function requestPermissions() {
    var data = {
        id: location.hash.substring(1),
        action: "getPermissions",
        csrfToken: $('#csrfToken').text()
    };
    $.post(
        "api/edit-user.php",
        JSON.stringify(data),
        function(data) {
            createTable(data);
        },
        "json"
    );
}
function createTable(data) {
    $('#permissions table>tbody').empty();
    $.each(data, function(index,item) {
        $('<tr></tr>').appendTo('#permissions table>tbody')
            .append('<td>' + item.name + '</td>')
            .append('<td><span class="glyphicon glyphicon-remove cursor-pointer"></span></td>')
            .data("id", item.id);       
    });
    $('#permissions table>tbody>tr>td>span.glyphicon-remove').click(removePermission);
}
function removePermission() {
    var data = {
        domainId: $(this).parent().parent().data("id"),
        userId: location.hash.substring(1),
        action: "removePermission",
        csrfToken: $('#csrfToken').text()
    };
    var lineToRemove = $(this).parent().parent();
    $.post(
        "api/edit-user.php",
        JSON.stringify(data),
        function(data) {
            $(lineToRemove).remove();
        },
        "json"
    );
}
function addPermissions() {
    var data = {
        action: "addPermissions",
        userId: location.hash.substring(1),
        domains: $('#permissions select#selectAdd').val(),
        csrfToken: $('#csrfToken').text()
    }
    $.post(
        "api/edit-user.php",
        JSON.stringify(data),
        function(data) {
            $('#permissions select#selectAdd').val(null).change();
            requestPermissions();
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