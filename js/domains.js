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
    requestData();
    
    $('#table-domains>thead>tr>td span').click(function() {
        var field = $(this).siblings('strong').text().toLowerCase();
        if(sort.field == field) {
            if(sort.order == 1) sort.order = 0;
            else sort.field = "";
        } else {
            sort.field = field;
            sort.order = 1;
        }
        $('#table-domains>thead>tr>td span').removeClass("glyphicon-sort-by-attributes glyphicon-sort-by-attributes-alt");
       
        if(sort.field == field) {
            if(sort.order == 1) $(this).addClass("glyphicon-sort-by-attributes");
            else $(this).addClass("glyphicon-sort-by-attributes-alt");
        }
        requestData();
    });

    $('#searchName').bind("paste keyup", function() {
        requestData();
    });

    $('#searchType').change(function() {
        requestData();
    });

    $('#searchType').select2({
        minimumResultsForSearch: Infinity
    });
});

function requestData(page) {
    if(typeof(page) !== 'number' || page <= 0) {
        page = 1;
    }
        
    var restrictions = {
        csrfToken: $('#csrfToken').text(),
    };

    restrictions.sort = sort;

    var searchName = $('#searchName').val();
    if(searchName.length > 0) {
        restrictions.name = searchName;
    }

    var searchType = $('#searchType').val();
    if(searchType != "none") {
        restrictions.type = searchType;
    }

    restrictions.action = "getDomains";
    restrictions.page = page;

    $.post(
        "api/domains.php",
        JSON.stringify(restrictions),
        function(data) {
            recreateTable(data.data);
            recreatePagination(data.pages)
        },
        "json"
    );
}

function recreatePagination(data) {
    $('#pagination').empty();
    
    if(data.total === 1) {
        $('#pagination-wrapper').hide();
        return;
    }
    
    if(data.current > 1) {
        $('<li><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>').appendTo('#pagination').data("page", data.current - 1).click(paginationClicked);
    }
    
    $('<li><span>1</span></li>').appendTo('#pagination').data("page", 1).click(paginationClicked);
    
    if(data.current > 4) {
        $('<li class="disabled"><span>&hellip;</span></li>').appendTo('#pagination');
    }
    
    for(var i = data.current - 2; i <= data.current + 2; i++) {
        if(i > 1 && i < data.total) {
            if(data.current === i) {
                $('<li class="active"><span>' + i + '</span></li>').appendTo('#pagination');
            } else {
                $('<li><span>' + i + '</span></li>').appendTo('#pagination').data("page", i).click(paginationClicked);
            }
        }
    }
    
    if(data.current < data.total - 3) {
        $('<li class="disabled"><span>&hellip;</span></li>').appendTo('#pagination');
    }
    
    $('<li><span>' + data.total + '</span></li>').appendTo('#pagination').data("page", data.total).click(paginationClicked);
    
    if(data.current < data.total) {
        $('<li><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>').appendTo('#pagination').data("page", data.current + 1).click(paginationClicked);
    }
    
    $('#pagination-wrapper').show();
}

function recreateTable(data) {
    $('#table-domains>tbody').empty();
    
    $.each(data, function(index,item) {
       $('<tr></tr>').appendTo('#table-domains>tbody')
            .append('<td>' + item.id + '</td>')
            .append('<td>' + item.name + '</td>')
            .append('<td>' + item.type + '</td>')
            .append('<td>' + item.records + '</td>')
            .append('<td><span class="glyphicon glyphicon-trash cursor-pointer"></span></td>');
       
    });
    
    $('#table-domains>tbody>tr>td:not(:last-child)').click(function() {
        var id = $(this).parent().children('td').first().text();
        var type = $(this).parent().children('td').eq(2).text();
        
        if(type == 'MASTER') {
            location.assign('edit-master.php#' + id);
        } else if(type == 'NATIVE') {
            location.assign('edit-master.php#' + id);
        }
    });
    
    $('#table-domains>tbody>tr>td>span.glyphicon-trash').click(function() {
        $(this).parent().parent().unbind();
        deleteDomain.call(this);
    });
}

function deleteDomain() {
    var deleteId = $(this).parent().parent().children('td').eq(0).text();
    var deleteZone = $(this).parent().parent().children('td').eq(1).text();
    var rowToRemove = $(this).parent().parent();
    
    $('#zoneToDelete').text(deleteZone);
    
    $('#deleteConfirm #buttonDelete').click(function() {
        deleteDomainWithId(deleteId, function() {
            $('#deleteConfirm').modal("hide");
            $(rowToRemove).remove();
        });
    });
    
    $('#deleteConfirm').modal();
}

function deleteDomainWithId(id, callback) {
    var data = {
        action: "deleteDomain",
        id: id,
        csrfToken: $('#csrfToken').text()
    };
    
    $.post(
        "api/domains.php",
        JSON.stringify(data),
        function() {
            callback();
        },
        "json"
    );
}

function paginationClicked() {
    requestData($(this).data("page"));
}