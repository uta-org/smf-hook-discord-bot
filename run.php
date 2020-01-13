<?php

require __DIR__.'/../composer-yasmin/vendor/autoload.php';
require 'db/db.conn.php';
require 'funcs/db.funcs.php';
// require 'funcs/globalization.funcs.php';
require 'funcs/dom.funcs.php';
require 'funcs/string.funcs.php';
require 'funcs/run.funcs.php';
require 'funcs/misc.funcs.php';
require 'funcs/test.funcs.php';

define('LOCK_FILE', "/var/run/" . basename($argv[0], ".php") . ".lock");

if (!tryLock())
    die("Already running.\n");

# remove the lock on exit (Control+C doesn't count as 'exit'?)
register_shutdown_function('unlink', LOCK_FILE);

# The rest of your script goes here....

$pid = getmypid();
echo "Hello world! Your PID is: ".$pid."\n";
file_put_contents('pid.txt', $pid);

// echo print_r($argv, true).PHP_EOL;

$loop = \React\EventLoop\Factory::create();
$client = new \CharlotteDunois\Yasmin\Client(array(), $loop);

$client->on('error', function ($error) {
    echo $error.PHP_EOL;
});

$client->on('ready', function () use ($client) {
    echo 'Logged in as '.$client->user->tag.' created on '.$client->user->createdAt->format('d.m.Y H:i:s').PHP_EOL;

    // TODO: Hook everything again when restarting bot
    // getCachedChannels($client);
});

$client->on('message', function ($message) use($client, $db) {
    echo 'Received Message from '.$message->author->tag.' in '.($message->channel instanceof \CharlotteDunois\Yasmin\Interfaces\DMChannelInterface ? 'DM' : 'channel #'.$message->channel->name ).' with '.$message->attachments->count().' attachment(s) and '.\count($message->embeds).' embed(s)'.PHP_EOL;
    $contents = $message->content;

    if($contents === '$list') {
        getCachedChannels($client);
    }

    if(startsWith($contents, '$start')) {
        startListening($db, $client, $message, getParams($contents));
    }

    if($contents === '$embed') {
        sendEmbedMessage($message->channel);
    }
});

$token = getenv("SMF_TOKEN");
$client->login($token)->done();
$loop->run();

sleep(30);
exit(0);