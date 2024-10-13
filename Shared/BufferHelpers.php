<?
//----------------------------------------------------------------------------------
//  FlushAndClose()
//
//  This function forces PHP to send the given contents to the browser and closes
//  the connection. This can be used in PHP file to send a response to the browser
//  and then continue processing a long-running process silently in the background
//  (such as sending a notification email)
//
//  IMPORTANT: Make sure gzip compression is not enabled for text/html on the
//  server. gzip compression forces the server to buffer the entire response
//  effectively neutralizing the commands below.
//
//  IMPORTANT: In IIS you must edit WINDOWS\system32\inetsrv\fcgiext.ini and
//  add the line ResponseBufferLimit=0 to the [PHP] section. Otherwise FastCGI
//  will do its own buffering effectively neutralizing the commands below. ob_start,
//  ob_end_flush(), etc. override the output_buffering settings in php.ini
//
//  PARAMETERS:
//    content     - content to be sent to browser
//
//  RETURN: none
//-----------------------------------------------------------------------------------
function FlushAndClose($content)
{
    // Close session write. Otherwise next page to load would be blocked on call to session_start()
    // while this page finished executing
    session_write_close();
    // Start buffering
    ob_start();
    // Load content into the buffer
    echo $content;
    // tell browser the length of the content and close the connection
    header("Content-Length: " . ob_get_length());
    header('Connection: close');
    // Flush output buffer. All three flush commands are needed.
    ob_end_flush();
    ob_flush();
    flush();
}
?>