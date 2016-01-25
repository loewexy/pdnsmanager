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
    
    $('#buttonInstall').click(function(evt){
        evt.preventDefault();
        checkSettings();
    });
    
    $('#adminPassword2').bind("change keyup paste", function() {
        if($('#adminPassword').val() == $('#adminPassword2').val()) {
            $(this).parent().removeClass("has-error");
        } else {
            $(this).parent().addClass("has-error");
        }
    })
});

function checkSettings() {
    
    if($('#adminPassword').val() != $('#adminPassword2').val()) {
        $('#adminPassword2').parent().addClass("has-error");
    }
    
    if($('#adminPassword').val().length <= 0) {
        $('#adminPassword').parent().addClass("has-error");
    }
    
    if($('#adminName').val().length <= 0) {
        $('#adminName').parent().addClass("has-error");
    }
    
    var data = {
        host: $('#dbHost').val(),
        user: $('#dbUser').val(),
        password: $('#dbPassword').val(),
        database: $('#dbDatabase').val(),
        port: $('#dbPort').val(),
        userName: $('#adminName').val(),
        userPassword: $('#adminPassword').val()
    };
    
    $.post(
        "api/install.php",
        JSON.stringify(data),
        function(data) {
            if(data.status == "error") {
                $('#alertFailed').text(data.message);
                $('#alertFailed').slideDown(600);
            } else if(data.status == "success") {
                location.assign("index.php");
            }
        },
        "json"
    );
}

