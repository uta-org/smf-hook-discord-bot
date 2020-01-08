<?php

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

function sendEmbedMessage($channel) {
     $embed = new \CharlotteDunois\Yasmin\Models\MessageEmbed();
            
    // Build the embed
    $embed
        ->setTitle('A new Rich Embed')                                                       // Set a title
        ->setColor(random_int(0, 16777215))                                                  // Set a color (the thing on the left side)
        ->setDescription(':)')                                                               // Set a description (below title, above fields)
        ->addField('Test', 'Value')                                                          // Add one field
        ->addField('Test 2', 'Value 2', true)                                                // Add one inline field
        ->addField('Test 3', 'Value 3', true)                                                // Add another inline field
        ->setThumbnail('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4')         // Set a thumbnail (the image in the top right corner)
        ->setImage('https://avatars1.githubusercontent.com/u/4529744?s=460&v=4')             // Set an image (below everything except footer)
        ->setTimestamp()                                                                     // Set a timestamp (gets shown next to footer)
        ->setAuthor('Yasmin', 'https://avatars1.githubusercontent.com/u/4529744?s=460&v=4')  // Set an author with icon
        ->setFooter('Generated with the Rich Embed Builder (Y)')                               // Set a footer without icon
        ->setURL('https://github.com/CharlotteDunois/Yasmin');                               // Set the URL
            
    // Send the message
            
    // We do not need another promise here, so
    // we call done, because we want to consume the promise
    $channel->send('', array('embed' => $embed))
            ->done(null, function ($error) {
                // We will just echo any errors for this example
                echo $error.PHP_EOL;
            });
}