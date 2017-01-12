$(function () {
    var FADE_TIME = 150; // ms
    var TYPING_TIMER_LENGTH = 400; // ms
    var COLORS = [
        '#e21400', '#91580f', '#f8a700', '#f78b00',
        '#58dc00', '#287b00', '#a8f07a', '#4ae8c4',
        '#3b88eb', '#3824aa', '#a700ff', '#d300e7'
    ];

    // Initialize variables
    var $window = $(window);
    var $usernameInput = $('.input_username'); // Input for username
    var $messages = $('.chat_messages'); // Messages area
    var $inputMessage = $('.input_message'); // Input message input box

    // Prompt for setting a username
    var username;
    var connected = false;
    var typing = false;
    var lastTypingTime;
    var $currentInput = $inputMessage.focus();

    var socket = io(window.location.hostname + ':8081');

    // Sets the client's username
    setUsername();
    function setUsername() {
        username = cleanInput($usernameInput.val().trim());

        // If the username is valid
        if (username) {
            $currentInput = $inputMessage.focus();

            socket.emit('add user');
        }
    }

    // Sends a chat message
    function sendMessage() {
        var message = $inputMessage.val();
        // Prevent markup from being injected into the message
        message = cleanInput(message);
        // if there is a non-empty message and a socket connection
        if (message && connected) {
            $inputMessage.val('');
            addChatMessage({
                username: username,
                message: message
            });
            // tell server to execute 'new message' and send along one parameter
            socket.emit('new message', {
                username: username,
                message: message
            });
        }
    }

    // Log a message
    function log(message, options) {
        var $el = $('<li>').addClass('chat_log').text(message);
        addMessageElement($el, options);
    }

    // Adds the visual chat message to the message list
    function addChatMessage(data, options) {
        // Don't fade the message in if there is an 'X was typing'
        var $typingMessages = getTypingMessages(data);
        options = options || {};
        if ($typingMessages.length !== 0) {
            options.fade = false;
            $typingMessages.remove();
        }

        var $usernameDiv = $('<span class="chat_username"/>')
                .text(data.username)
                .css('color', getUsernameColor(data.username));
        var $messageBodyDiv = $('<span class="messageBody">')
                .text(data.message);

        var typingClass = data.typing ? 'typing' : '';
        var $messageDiv = $('<li class="message"/>')
                .data('username', data.username)
                .addClass(typingClass)
                .append($usernameDiv, $messageBodyDiv);

        addMessageElement($messageDiv, options);
    }

    // Adds the visual chat typing message
    function addChatTyping(data) {
        data.typing = true;
        data.message = 'tippt...';
        addChatMessage(data);
    }

    // Removes the visual chat typing message
    function removeChatTyping(data) {
        getTypingMessages(data).fadeOut(function () {
            $(this).remove();
        });
    }

    // Adds a message element to the messages and scrolls to the bottom
    // el - The element to add as a message
    // options.fade - If the element should fade-in (default = true)
    // options.prepend - If the element should prepend
    //   all other messages (default = false)
    function addMessageElement(el, options) {
        var $el = $(el);

        // Setup default options
        if (!options) {
            options = {};
        }
        if (typeof options.fade === 'undefined') {
            options.fade = true;
        }
        if (typeof options.prepend === 'undefined') {
            options.prepend = false;
        }

        // Apply options
        if (options.fade) {
            $el.hide().fadeIn(FADE_TIME);
        }
        if (options.prepend) {
            $messages.prepend($el);
        } else {
            $messages.append($el);
        }
        $messages[0].scrollTop = $messages[0].scrollHeight;
    }

    // Prevents input from having injected markup
    function cleanInput(input) {
        return $('<div/>').text(input).text();
    }

    // Updates the typing event
    function updateTyping() {
        if (connected) {
            if (!typing) {
                typing = true;
                socket.emit('typing', username);
            }
            lastTypingTime = (new Date()).getTime();

            setTimeout(function () {
                var typingTimer = (new Date()).getTime();
                var timeDiff = typingTimer - lastTypingTime;
                if (timeDiff >= TYPING_TIMER_LENGTH && typing) {
                    socket.emit('stop typing', username);
                    typing = false;
                }
            }, TYPING_TIMER_LENGTH);
        }
    }

    // Gets the 'X is typing' messages of a user
    function getTypingMessages(data) {
        return $('.typing.message').filter(function (i) {
            return $(this).data('username') === data.username;
        });
    }

    function hashCode(str) {
        var hash = 0, i, chr, len;
        if (str.length === 0)
            return hash;
        for (i = 0, len = str.length; i < len; i++) {
            chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0;
        }
        return hash;
    }

    var onlineUsers = [];

    function showAsOnline(data) {
        var $usernameDiv = $('<span class="chat_username"/>')
                .text(data.username)
                .css('color', getUsernameColor(data.username));

        var $messageDiv = $('<li class="message ' + hashCode(data.username) + '"/>')
                .data('username', data.username)
                .append($usernameDiv);

        $('#online:not(:has(.message.' + hashCode(data.username) + '))').append($messageDiv);
        onlineUsers.push(data.username);
    }

    setInterval(function () {
        $('#online .message').each(function () {
            if ($.inArray($(this).data('username'), onlineUsers) === -1) {
                $(this).remove();
            }
        });
        onlineUsers = [];
    }, 2000);

    // Gets the color of a username through our hash function
    function getUsernameColor(username) {
        // Compute hash code
        var hash = 7;
        var index = 42;
        for (var i = 0; i < username.length; i++) {
            hash = username.charCodeAt(i) + (hash << 5) - hash;
        }
        // Calculate color
        index = Math.abs(hash % COLORS.length);
        return COLORS[index];
    }

    // Keyboard events

    $window.keydown(function (event) {
        // Auto-focus the current input when a key is typed
        if (!(event.ctrlKey || event.metaKey || event.altKey)) {
            $currentInput.focus();
        }
        // When the client hits ENTER on their keyboard
        if (event.which === 13) {
            if (username) {
                sendMessage();
                socket.emit('stop typing', username);
                typing = false;
            } else {
                setUsername();
            }
        }
    });

    $inputMessage.on('input', function () {
        updateTyping();
    });

    // Socket events

    // Whenever the server emits 'login', log the login message
    socket.on('login', function () {
        connected = true;
        log("Nachrichten in diesem Chat werden nicht gespeichert.", {prepend: true});
        log("Diesen Chat können ALLE Nutzer sehen.", {prepend: true});
        log("Willkommen im MyEnvoy Chat", {prepend: true});
    });

    // Whenever the server emits 'new message', update the chat body
    socket.on('new message', function (data) {
        addChatMessage({
            username: data.message.username,
            message: data.message.message
        });
    });

    // Whenever the server emits 'typing', show the typing message
    socket.on('typing', function (data) {
        addChatTyping(data);
    });

    // Whenever the server emits 'stop typing', kill the typing message
    socket.on('stop typing', function (data) {
        removeChatTyping(data);
    });

    socket.on('online', function (data) {
        showAsOnline(data);
    });

    setInterval(function () {
        socket.emit('online', username);
    }, 2000);
});
