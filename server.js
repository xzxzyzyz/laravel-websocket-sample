var server = require('http').createServer(handler);
var io = require('socket.io')(server);
var redis = require('redis');

server.listen(8890, function() {
    console.log('running...');
});

function handler(req, res) {
    res.writeHead(404);
    res.end('Not Found');
}

io.on('connection', function (socket) {

    console.log("new client connected");
    var redisClient = redis.createClient();

    redisClient.psubscribe('*', function(err, count) {
        //
    });

    redisClient.on('pmessage', function(subscribed, channel, message) {
        var message = JSON.parse(message);
        socket.emit('channel-' + channel, message.data);
    })

    socket.on('disconnect', function() {
        console.log('disconnect');
        redisClient.quit();
    });

});