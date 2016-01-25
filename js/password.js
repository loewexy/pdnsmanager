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
    
    $('#saveChanges').click(function(evt){
        evt.preventDefault();
        savePassword();
    });
    
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
});

function savePassword() {
    
    if($('#user-password').val().length <= 0) {
        $('#user-password').parent().addClass("has-error");
        $('#user-password2').parent().addClass("has-error");
    }
    if($('#user-password2').parent().hasClass("has-error")) {
        return;
    }    
    
    var data = {
        password: $('#user-password').val(),
        action: "changePassword"
    };
    
    $.post(
        "api/password.php",
        JSON.stringify(data),
        function(data) {
            $('#user-password').val("");
            $('#user-password2').val("");
        },
        "json"
    );
}

