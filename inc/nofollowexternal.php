<?php
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("parse_message_end", "nofollowexternal_changelink");

function nofollowexternal_info() {
    return array(
        "name" => "Nofollow external",
        "description" => "A simple plugin that puts rel=\"nofollow\" in external links",
        "website" => "",
        "author" => "Matthew DeSantis",
        "authorsite" => "http://www.anarchy46.net/",
        "version" => "1.1",
        "guid"  => "2b874bd1e9ee6153673957629d68ef12",
        "compatibility" => "*"
        );
}
function nofollowexternal_changelink($message) {
    global $mybb;
    $message = preg_replace_callback("/<a href=\"(.+)\" (.+)>(.+)<\\/a>/",  "replace_external", $message);
    return $message;
}
function replace_external($groups) {
    global $mybb;
    $url = preg_quote($mybb->settings['bburl']);
    $urls = preg_replace("/^http:/","https:", $url, 1);
    if (preg_match("/$url/", $groups[1]) || preg_match("/$urls/", $groups[1])) {
        return $groups[0];
    }
    else {
        return "<a href=\"".$groups[1]."\" target=\"_blank\" rel=\"nofollow\">".$groups[3]."</a>";
    }
}
?>
