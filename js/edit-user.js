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
    }
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
    }
    
    if($('#user-name').parent().hasClass("has-error")) {
        return;
    }
    if($('#user-password2').parent().hasClass("has-error")) {
        return;
    }    
    
    var data = {
        name: $('#user-name').val(),
        password: $('#user-password').val(),
        type: $('#user-type').val(),
        action: "addUser"
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
        action: "getUserData"
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
    var data = {
        id: location.hash.substring(1),
        name: $('#user-name').val(),
        type: $('#user-type').val(),
        action: "saveUserChanges"
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