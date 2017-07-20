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
    $('#data-password-password2').bind("paste keyup change", function() {
        if($('#data-password-password').val() != $('#data-password-password2').val()) {
            $(this).parent().addClass("has-error");
        } else {
            $(this).parent().removeClass("has-error");
        }
    });
    $('#button-add-password').click(function() {
        resetFields();
        $('#data-password').show();
        $('#data-key').hide();
        $('#data-password-confirm').unbind().click(addPassword);
    });
    $('#button-add-key').click(function() {
        resetFields();
        $('#data-key').show();
        $('#data-password').hide();
        $('#data-key-confirm').unbind().click(addKey);
    });
    $('#data-password-cancel').click(function() {
        $('#data-password').hide();
    });
    $('#data-key-cancel').click(function() {
        $('#data-key').hide();
    });
    requestPermissions();
});
function regexValidate() {
    var regex = new RegExp($(this).attr('data-regex'));
    if(!regex.test($(this).val())) {
        $(this).parent().addClass("has-error");
    } else {
        $(this).parent().removeClass("has-error"); 
    }
}
function createTable(data) {
    $('#permissions tbody').empty();
    $.each(data, function(index,item) {
        $('<tr></tr>').appendTo('#permissions tbody')
            .append('<td>' + item.id + '</td>')
            .append('<td>' + item.description + '</td>')
            .append('<td>' + item.type + '</td>')
            .append('<td><span class="glyphicon glyphicon-pencil cursor-pointer"></span></td>')
            .append('<td><span class="glyphicon glyphicon-trash cursor-pointer"></span></td>');     
    });
    $('#permissions tbody span.glyphicon-trash').click(deletePermission);
    $('#permissions tbody span.glyphicon-pencil').click(prepareEdit);
}
function requestPermissions() {
    var data = {
        action: "getPermissions",
        csrfToken: $('#csrfToken').text(),
        record: location.hash.substring(1)
    };
    $.post(
        "api/edit-remote.php",
        JSON.stringify(data),
        function(data) {
            createTable(data);
        },
        "json"
    );
}
function resetFields() {
    $('#info-dialogs input').val("");
    $('#info-dialogs textarea').val("");
    $('#info-dialogs .form-group').removeClass("has-error");
    $('#data-password-password').attr("placeholder", "Password");
    $('#data-password-password2').attr("placeholder", "Password repeated");
    $('#data-password-confirm').text("Add");
    $('#data-key-confirm').text("Add");
}
function addPassword() {
    if($('#data-password-password').val() != $('#data-password-password2').val() || $('#data-password-password').val().length <= 0) {
        $('#data-password-password2').parent().addClass("has-error");
        shake($('#data-password-confirm'));
        return;
    }
    var data = {
        csrfToken: $('#csrfToken').text(),
        action: "addPassword",
        description: $('#data-password-description').val(),
        password: $('#data-password-password').val(),
        record: location.hash.substring(1)
    };
    $.post(
        "api/edit-remote.php",
        JSON.stringify(data),
        function(data) {
            $('#data-password').hide();
            requestPermissions();
        },
        "json"
    );
}
function addKey() {
    if($('#data-key-key').val().length <= 0) {
        $('#data-key-key').parent().addClass("has-error");
        shake($('#data-key-confirm'));
        return;
    }
    var data = {
        csrfToken: $('#csrfToken').text(),
        action: "addKey",
        description: $('#data-key-description').val(),
        key: $('#data-key-key').val(),
        record: location.hash.substring(1)
    };
    $.post(
        "api/edit-remote.php",
        JSON.stringify(data),
        function(data) {
            $('#data-key').hide();
            requestPermissions();
        },
        "json"
    );
}
function deletePermission() {
    var data = {
        csrfToken: $('#csrfToken').text(),
        action: "deletePermission",
        permission: $(this).parent().siblings().eq(0).text(),
        record: location.hash.substring(1)
    };
    $.post(
        "api/edit-remote.php",
        JSON.stringify(data),
        function(data) {
            requestPermissions();
        },
        "json"
    );
}
function prepareEdit() {
    var type = $(this).parent().siblings().eq(2).text();
    if(type === "password") {
        resetFields();
        $('#data-password').show();
        $('#data-key').hide();
        $('#data-password-confirm').unbind().click(changePassword);
        $('#data-password-password').attr("placeholder", "(Unchanged)");
        $('#data-password-password2').attr("placeholder", "(Unchanged)");
        $('#data-password-confirm').text("Change");
        $('#data-password-description').val($(this).parent().siblings().eq(1).text());
        $('#data-password-confirm').data("permission-id", $(this).parent().siblings().eq(0).text());
    } else if(type === "key") {
        resetFields();
        $('#data-key').show();
        $('#data-password').hide();
        $('#data-key-confirm').unbind().click(changeKey);
        $('#data-key-confirm').text("Change");
        $('#data-key-description').val($(this).parent().siblings().eq(1).text());
        $('#data-key-confirm').data("permission-id", $(this).parent().siblings().eq(0).text());
        var data = {
            csrfToken: $('#csrfToken').text(),
            action: "getKey",
            permission: $(this).parent().siblings().eq(0).text(),
            record: location.hash.substring(1)
        };
        $.post(
            "api/edit-remote.php",
            JSON.stringify(data),
            function(data) {
                $('#data-key-key').val(data.key);
            },
            "json"
        );
    }
}
function changePassword() {
    if($('#data-password-password').val() != $('#data-password-password2').val()) {
        $('#data-password-password2').parent().addClass("has-error");
        return;
    }
    var data = {
        csrfToken: $('#csrfToken').text(),
        action: "changePassword",
        description: $('#data-password-description').val(),
        record: location.hash.substring(1),
        permission: $('#data-password-confirm').data("permission-id")
    };
    if($('#data-password-password').val().length >= 0) {
        data.password = $('#data-password-password').val();
    }
    $.post(
        "api/edit-remote.php",
        JSON.stringify(data),
        function(data) {
            $('#data-password').hide();
            requestPermissions();
        },
        "json"
    );
}
function changeKey() {
    if($('#data-key-key').val().length <= 0) {
        $('#data-key-key').parent().addClass("has-error");
        return;
    }
    var data = {
        csrfToken: $('#csrfToken').text(),
        action: "changeKey",
        description: $('#data-key-description').val(),
        key: $('#data-key-key').val(),
        record: location.hash.substring(1),
        permission: $('#data-key-confirm').data("permission-id")
    };
    $.post(
        "api/edit-remote.php",
        JSON.stringify(data),
        function(data) {
            $('#data-key').hide();
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