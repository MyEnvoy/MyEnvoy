var express = require('express');
var app = express();
var server = require('http').createServer(app);
var io = require('socket.io')(server);
var port = 8080;

server.listen(port, function () {
    console.log('Server listening at port %d', port);
});

io.on('connection', function (socket) {
    var addedUser = false;

    socket.on('new message', function (data) {
        socket.broadcast.emit('new message', {
            message: data
        });
    });

    socket.on('add user', function () {
        if (addedUser)
            return;

        addedUser = true;
        socket.emit('login');
    });

    socket.on('typing', function (username) {
        socket.broadcast.emit('typing', {
            username: username
        });
    });

    socket.on('stop typing', function (username) {
        socket.broadcast.emit('stop typing', {
            username: username
        });
    });
    
    socket.on('online', function(username){
        socket.broadcast.emit('online', {
            username: username
        });
    });
});