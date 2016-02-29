$(window).load(function () {

    var is_searching = false;
    var typed = false;

    $('input#search_box').bind('keyup', function () {
        if (is_searching === true) {
            typed = true;
            return;
        }

        if ($('#search_box').val().length >= 3) {
            is_searching = true;
            $('div#loading').show();

            $.ajax({
                type: 'POST',
                url: 'search.do',
                data: {s: $('#search_box').val()}
            }).done(function (jsonData) {
                var json = $.parseJSON(jsonData);

                $('#results').empty();

                var $item = '';
                for (i = 0; i < json.length; i++) {
                    $item = '';

                    $item = '<div class="col dashboard_search_resultcontainer">\
                        <div class="dashboard_search_resultwrapper">\
                            <div class="row">\
                                <div class="col one">\
                                    <img class="profile_pic" src="' + json[i].icon + '" alt="Profile picture" width="32" height="32">\
                                </div>\
                                <div class="col nine dashboard_search_username">\
                                    <a href="' + json[i].url + '" class="text_bold">' + json[i].name + '</a><br>\
                                    <span class="text_light">@' + json[i].server + '</span>\
                                </div>\
                            </div>\
                        </div>\
                    </div>';

                    $('#results').append($item);
                }

                finishRequest();
            }).fail(function () {
                finishRequest();
            });
        }
    });

    function finishRequest() {
        $('div#loading').hide();
        is_searching = false;
        if (typed) {
            typed = false;
            $('input#search_box').trigger('keyup');
        }
    }

});