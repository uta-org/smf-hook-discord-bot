<?php

define('LOCK_FILE', "/var/run/" . basename($argv[0], ".php") . ".lock");

if (!tryLock())
    die("Already running.\n");

# remove the lock on exit (Control+C doesn't count as 'exit'?)
register_shutdown_function('unlink', LOCK_FILE);

# The rest of your script goes here....
$pid = getmypid();
echo "Hello world! Your PID is: ".$pid."\n";
file_put_contents('pid.txt', $pid);

require __DIR__.'/../composer-yasmin/vendor/autoload.php';

// Include composer autoloader

$loop = \React\EventLoop\Factory::create();
$client = new \CharlotteDunois\Yasmin\Client(array(), $loop);

$client->on('error', function ($error) {
    echo $error.PHP_EOL;
});

$client->on('ready', function () use ($client) {
    echo 'Logged in as '.$client->user->tag.' created on '.$client->user->createdAt->format('d.m.Y H:i:s').PHP_EOL;
});

$client->on('message', function ($message) {
    echo 'Received Message from '.$message->author->tag.' in '.($message->channel instanceof \CharlotteDunois\Yasmin\Interfaces\DMChannelInterface ? 'DM' : 'channel #'.$message->channel->name ).' ['.$message->channel->getId().'] with '.$message->attachments->count().' attachment(s) and '.\count($message->embeds).' embed(s)'.PHP_EOL;
    if($message->content === '$ping') {
        $message->channel->send('Pong!');
    	// echo serialize($message->channel);
    }

    if($message->content === '$list') {
        getCachedChannels($client);
    }
});

$token = getenv("SMF_TOKEN");
$client->login($token)->done();
$loop->run();

sleep(30);

exit(0);

function getCachedChannels($client) {
    foreach ($channel as $client->channels) {
        echo $channel->getId();
    }
}

function tryLock()
{
    # If lock file exists, check if stale.  If exists and is not stale, return TRUE
    # Else, create lock file and return FALSE.

    if (@symlink("/proc/" . getmypid(), LOCK_FILE) !== FALSE) # the @ in front of 'symlink' is to suppress the NOTICE you get if the LOCK_FILE exists
        return true;

    # link already exists
    # check if it's stale
    if (is_link(LOCK_FILE) && !is_dir(LOCK_FILE))
    {
        unlink(LOCK_FILE);
        # try to lock again
        return tryLock();
    }

    return false;
}
?>
