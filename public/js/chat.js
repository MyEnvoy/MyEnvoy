/* global Strophe, prosodyJid, prosodySid, prosodyRid, $pres, $msg */

var connection = null;
var heads = getStorage('prosody_heads') || [];
var headMsgCount = getStorage('prosody_heads_count') || [];

var host = window.location.hostname.replace('www.', '');

var chatwindows = [];
var collapsedwins = getStorage('prosody_collapsed') || [];

$(document).ready(function () {

    connection = new Strophe.Connection('/http-bind');

    connection.attach(prosodyJid,
            prosodySid,
            prosodyRid,
            onStatus);

    var chats = getStorage('prosody_windows') || [];
    for (var i = 0; i < chats.length; i++) {
        openChatWindow(chats[i], chats[i] + '@' + host);
    }

    updateRoster();

    $('body').tooltip({
        selector: '[data-toggle=tooltip]',
        container: 'body'
    });

    $(window).bind('beforeunload', function () {
        connection.options.sync = true;
        connection.flush();
        connection.disconnect();
    });

    $('span#prosody_chatbar_roster_controller_btn').click(function () {
        $('div#prosody_chatbar_container').toggleClass('collapsed');
        $.cookie('prosody_bar_closed', $('div#prosody_chatbar_container').hasClass('collapsed'), {path: '/'});
    });

    $(document).on('click', 'div.prosody_userhead', function () {
        var name = $(this).data('name');
        $('div#prosody_chatbar_roster_wrapper_scroll')
                .find('.prosody_userhead[data-name="' + name + '"]')
                .find('span').remove();
        headMsgCount[heads.indexOf(name)] = 0;
        saveStorage('prosody_heads_count', headMsgCount);
        openChatWindow(name, $(this).data('jid'));

        var chatwin = $('div.prosody_chatwindow[data-name="' + name + '"]');
        chatwin.removeClass('collapsed');
        chatwin.find('div.prosody_chatwindow_newmsg').find('input').focus();
        collapsedwins[chatwindows.indexOf(name)] = false;
        saveStorage('prosody_collapsed', collapsedwins);
    });

    $(document).on('click', 'a.prosody_userhead_searchres', function (e) {
        var name = $(e.target).data('name');
        if (heads.indexOf(name) === -1) {
            heads.push(name);
            headMsgCount.push(0);
            saveStorage('prosody_heads', heads);
            saveStorage('prosody_heads_count', headMsgCount);
            updateRoster();
        }
    });

    $(document).on('click', 'span.prosody_chatwindow_close', function () {
        var name = $(this).data('name');
        $('div.prosody_chatwindow[data-name="' + name + '"]').remove();
        chatwindows.splice(chatwindows.indexOf(name), 1);
        collapsedwins.splice(chatwindows.indexOf(name), 1);
        saveStorage('prosody_windows', chatwindows);
        saveStorage('prosody_collapsed', collapsedwins);
    });

    $(document).on('click', 'div.prosody_chatwindow_head', function (e) {
        var parent = $(e.target).parent();
        var name = parent.data('name');
        parent.toggleClass('collapsed');
        $('div.prosody_chatwindow[data-name="' + name + '"]').removeClass('newmsg');
        collapsedwins[chatwindows.indexOf(name)] = !collapsedwins[chatwindows.indexOf(name)];
        saveStorage('prosody_collapsed', collapsedwins);
        if (!collapsedwins[chatwindows.indexOf(name)]) {
            parent.find('div.prosody_chatwindow_newmsg').find('input').focus();
            headMsgCount[heads.indexOf(name)] = 0;
            saveStorage('prosody_heads_count', headMsgCount);
        }
    });

    $(document).on('keydown', 'input.prosody_chatwindow_newmsg_input', function (e) {
        if (e.keyCode === 13) {
            var to = $(this).data('to');
            var val = $(this).val();
            sendMessage(to, val);
            storeMessage(getNameFromJid(prosodyJid), val);
            var msg = {
                time: Date.now(),
                msg: val
            };
            appendMessage(getNameFromJid(to), msg, true);
            $(this).val('');
        }
    });

    var is_searching = false;
    var typed = false;

    $('input#prosody_chatbar_search_input').bind('keyup', function () {
        if (is_searching === true) {
            typed = true;
            return;
        }

        if ($('#prosody_chatbar_search_input').val().length >= 3) {
            is_searching = true;
            $('div#prosody_loading').show();

            $.ajax({
                type: 'POST',
                url: '/de/dashboard/search.do',
                data: {s: $('#prosody_chatbar_search_input').val()}
            }).done(function (jsonData) {
                var json = $.parseJSON(jsonData);

                $('#prosody_chatbar_search_results').empty();

                var $item = '';
                for (i = 0; i < json.length; i++) {
                    $item = '';

                    $item = '<div class="dashboard_search_resultwrapper">\n\
                            <div class="row">\n\
                                <div class="col one">\n\
                                    <img class="profile_pic" src="' + json[i].icon + '" alt="Profile picture" width="32" height="32">\n\
                                </div>\n\
                                <div class="col one"></div>\n\
                                <div class="col eight dashboard_search_username">\n\
                                    <a class="text_bold prosody_userhead_searchres" data-name="' + json[i].name + '">' + json[i].name + '</a>\n\
                                </div>\n\
                            </div>\n\
                        </div>';

                    $('#prosody_chatbar_search_results').append($item);
                }

                finishRequest();
            }).fail(function () {
                finishRequest();
            });
        }
    });

    function finishRequest() {
        $('div#prosody_loading').hide();
        is_searching = false;
        if (typed) {
            typed = false;
            $('input#prosody_chatbar_search_input').trigger('keyup');
        }
    }

});

function onStatus(status) {
    if (status === Strophe.Status.CONNECTING) {
        console.log('Strophe is connecting.');
    } else if (status === Strophe.Status.CONNFAIL) {
        console.log('Strophe failed to connect.');
    } else if (status === Strophe.Status.DISCONNECTING) {
        console.log('Strophe is disconnecting.');
    } else if (status === Strophe.Status.DISCONNECTED) {
        console.log('Strophe is disconnected.');
    } else if (status === Strophe.Status.ATTACHED) {
        console.log('Strophe is attached.');

        connection.addHandler(onMessage, null, 'message', null, null, null);
        connection.send($pres().tree());
    }
}

function onMessage(msg) {
    var from = msg.getAttribute('from');
    var type = msg.getAttribute('type');
    var elems = msg.getElementsByTagName('body');

    var composing = msg.getElementsByTagName('composing');
    var paused = msg.getElementsByTagName('paused');

    if (type === 'chat' && elems.length > 0) {
        var body = elems[0];
        var msg = storeMessage(from, Strophe.getText(body));
        var name = getNameFromJid(from);
        appendMessage(name, msg);
        if (heads.indexOf(name) === -1) {
            heads.push(name);
            headMsgCount.push(0);
            saveStorage('prosody_heads', heads);
            saveStorage('prosody_heads_count', headMsgCount);
            updateRoster();
        }
        if (chatwindows.indexOf(name) === -1) {
            incrementHeadMsgCount(name);
        } else if ($('div.prosody_chatwindow[data-name="' + name + '"]').hasClass('collapsed')) {
            headMsgCount[heads.indexOf(name)]++;
            saveStorage('prosody_heads_count', headMsgCount);
            $('div.prosody_chatwindow[data-name="' + name + '"]').addClass('newmsg');
        }
        playSound();
    } else if (type === 'chat' && composing.length > 0) {
        console.log(from, 'tippt');
    } else if (type === 'chat' && paused.length > 0) {
        console.log(from, 'tippt nicht mehr');
    }

    return true;
}

function sendMessage(to, msg) {
    connection.send(
            $msg({to: to, type: 'chat'})
            .cnode(Strophe.xmlElement('body', msg)).up()
            .c('active', {xmlns: "http://jabber.org/protocol/chatstates"}));
}

function incrementHeadMsgCount(name) {
    var head = $('div#prosody_chatbar_roster_wrapper_scroll')
            .find('.prosody_userhead[data-name="' + name + '"]')
            .find('span');
    if (head.length === 0) {
        $('div#prosody_chatbar_roster_wrapper_scroll')
                .find('.prosody_userhead[data-name="' + name + '"]')
                .append('<span>0</span>');
        incrementHeadMsgCount(name);
        return;
    }
    headMsgCount[heads.indexOf(name)]++;
    saveStorage('prosody_heads_count', headMsgCount);
    head.html(headMsgCount[heads.indexOf(name)]);
}

function storeMessage(from, msg) {
    var msgs = getStorage('prosody_messages');

    if (msgs === null) {
        msgs = [];
    }

    var msg = {
        from: getNameFromJid(from),
        otherusr: getNameFromJid(from),
        time: Date.now(),
        msg: msg
    };

    msgs.push(msg);

    saveStorage('prosody_messages', msgs);
    return msg;
}

function openChatWindow(name, jid) {
    if (chatwindows.indexOf(name) === -1) {
        chatwindows.push(name);
        if (collapsedwins.length < chatwindows.length) {
            collapsedwins.push(false);
        }

        $('div#prosody_chatwindow_container').append('<div class="prosody_chatwindow '
                + (collapsedwins[chatwindows.indexOf(name)] ? 'collapsed' : '')
                + (headMsgCount[heads.indexOf(name)] > 0 ? ' newmsg' : ' ')
                + '" data-name="' + name + '">\n\
                <div class="prosody_chatwindow_head">\n\
                    <span class="prosody_chatwindow_name">' + name + '</span>\n\
                    <span class="prosody_chatwindow_close genericon genericon-close-alt" data-name="' + name + '"></span>\n\
                </div>\n\
                <div class="prosody_chatwindow_content">\n\
                    <div class="prosody_chatwindow_scroll"></div>\n\
                </div>\n\
                <div class="prosody_chatwindow_newmsg">\n\
                    <input class="prosody_chatwindow_newmsg_input" data-to="' + jid + '" type="text" placeholder="...">\n\
                </div>\n\
            </div>');
        saveStorage('prosody_windows', chatwindows);
        saveStorage('prosody_collapsed', collapsedwins);
        loadMessagesFromStorage(name);
    }
}

function loadMessagesFromStorage(name) {
    var msgs = getStorage('prosody_messages');

    for (var i = 0; i < msgs.length; i++) {
        if (msgs[i].from === name) {
            appendMessage(name, msgs[i]);
        } else if (msgs[i].from === getNameFromJid(prosodyJid)) {
            appendMessage(name, msgs[i], true);
        }
    }
}

function appendMessage(name, msg, me = false) {
    if (chatwindows.indexOf(name) !== -1) {
        if(me) {
            msg.msg = escapeHtml(msg.msg);
        }
        var el = $('div.prosody_chatwindow[data-name="' + name + '"]')
                .find('div.prosody_chatwindow_content')
                .find('div.prosody_chatwindow_scroll');
        var html = '<div class="mes_message_wrapper">\n';
        if (me === false) {
            html += '<div class="mes_message_head"><img src="/de/upload/userpic/?size=32&name=' + name + '"></div>\n';
        }
        html += '<div class="mes_message ' + (me ? 'mes_me' : '') + '">\n\
                                <div class="mes_message_content">' + msgFormat(msg.msg) + '</div>\n\
                                <div class="mes_message_footer">' + getTimeString(msg.time) + '</div>\n\
                            </div>\n\
                        </div>';
        el.append(html);
        scrollToEnd(el);
}
}

function msgFormat(msg) {
    return msg.replace(/(https?:\/\/?[\d\w\.-]+\.[\w\.]{2,6}[^\s\]\[\<\>]*\/?)/gi, '<a target="_blank" href="$1">$1</a>');
}

function escapeHtml(text) {
    return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
}

function scrollToEnd(el) {
    el.scrollTop(el.prop('scrollHeight'));
}

function getTimeString(stamp) {
    var date = new Date(stamp);

    var month = "0" + (date.getMonth() + 1);
    var day = "0" + date.getDate();
    var year = date.getFullYear();
    var hours = "0" + date.getHours();
    var minutes = "0" + date.getMinutes();

    return day.substr(day.length - 2) + '.' + month.substr(month.lengh - 2) + '.' + year + ' ' + hours.substr(hours.length - 2) + ':' + minutes.substr(minutes.length - 2);
}

function saveStorage(key, data) {
    localStorage.setItem(key, JSON.stringify(data));
}

function getStorage(key) {
    var val = localStorage.getItem(key);
    if (val === null)
        return null;
    return JSON.parse(val);
}

function updateRoster() {
    for (var i = 0; i < heads.length; i++) {
        var name = heads[i];
        var jid = name + '@' + host;

        var el = $('div#prosody_chatbar_roster_wrapper_scroll').has('.prosody_userhead[data-jid="' + jid + '"]');

        if (el.length === 0) {
            var content = '<div class="prosody_userhead" data-name="' + name + '" data-jid="' + jid + '" data-toggle="tooltip" data-placement="left" title="" data-original-title="' + name + '">\n\
                                <img src="/de/upload/userpic/?size=32&name=' + name + '">\n';
            if (headMsgCount[i] > 0 && !collapsedwins[chatwindows.indexOf(name)]) {
                content += '<span>' + headMsgCount[i] + '</span>';
            }
            content += '</div>';
            $('div#prosody_chatbar_roster_wrapper_scroll').append(content);
        }
    }
}

function getNameFromJid(jid) {
    return jid.split('@')[0];
}

var audioElement = null;

function playSound() {
    if (audioElement === null) {
        audioElement = new Audio('');
        document.body.appendChild(audioElement);
    }

    // get mediatype
    var canPlayType = audioElement.canPlayType('audio/wav');
    if (canPlayType.match(/maybe|probably/i)) {
        audioElement.src = '/js/notification.wav';
    }

    // play if mp3 is downloaded
    audioElement.addEventListener('canplay', function () {
        audioElement.play();
    }, false);
}