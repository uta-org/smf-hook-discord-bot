<?php

function sendMessage($channel, $title, $description, $image, $author, $author_avatar, $footer, $url) {
    echo "Sending embed message...".PHP_EOL;

    $embed = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
            
    $embed
        ->setTitle($title)
        ->setColor(random_int(0, 16777215))
        ->setDescription($description)
        ->setThumbnail($image)
        ->setImage($image)
        ->setTimestamp()
        ->setAuthor($author, $author_avatar)
        ->setFooter($footer)
        ->setURL($url);                               
            
    $channel->send('', array('embed' => $embed))
            ->done(null, function ($error) {
                echo $error.PHP_EOL;
            });
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