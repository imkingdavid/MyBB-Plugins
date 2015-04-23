<?php
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("parse_message_end", "regonlybbcode_get");

function regonlybbcode_info() {
    return array(
        "name"          => "Registered only bbcode",
        "description"   => "A simple plugin that makes it so only registered members can view text in certain bbcode",
        "website"       => "",
        "author"        => "Matthew DeSantis",
        "authorsite"    => "http://www.anarchy46.net/",
        "version"       => "1.1",
        "guid"          => "f5cec23b731e094ea29d54b769909c07",
        "compatibility" => "*"
    );
}
function regonlybbcode_install() {
    global $db;
    $query = $db->simple_select("settinggroups", "COUNT(*) as rows");
    $rows = $db->fetch_field($query, "rows");
    $db->insert_query('settinggroups', array(
        'gid'           => 'NULL',
        'name'          => 'regonlybbcode',
        'title'         => 'Registered Only BBCode Settings',
        'description'   => 'Seetings for Regonly BBCode',
        'disporder'     => $rows+1,
        'isdefault'     => 'no',
    ));
    $gid = $db->insert_id();
    $db->insert_query('settings', array(
        'name'          => 'regonlybbcodetag',
        'title'         => 'BBCode Tag Name',
        'description'   => 'Sets the tagname for regonly view',
        'optionscode'   => 'text',
        'value'         => 'paranoid',
        'disporder'     => 1,
        'gid'           => intval($gid),
    ));
    $db->insert_query('settings', array(
        'name'          => 'regonlybbcodedeny',
        'title'         => 'Deny message',
        'description'   => 'Message displayed when user is denied',
        'optionscode'   => 'text',
        'value'         => 'Only Registered members can view this',
        'disporder'     => 1,
        'gid'           => intval($gid),
    ));
    rebuild_settings();
}
function regonlybbcode_uninstall() {
    global $db;
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('regonlybbcodetag', 'regonlybbcodedeny')");
    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='regonlybbcode'");
    rebuild_settings(); 
}
function regonlybbcode_is_installed() {
    global $mybb;
    //return true;
    return array_key_exists('regonlybbcodetag', $mybb->settings);
}
function regonlybbcode_get($message) {
    global $mybb;
    $tag = preg_quote($mybb->settings['regonlybbcodetag']);
    if ($tag != "") $message = preg_replace_callback("/\\[".$tag."\\](.+)\\[\\/".$tag."\\]/siU",  "regonly_display", $message);
    return $message;
}
function regonly_display($groups) {
    global $mybb;
    if ($mybb->usergroup['gid'] == 1 || $mybb->usergroup['isbannedgroup']) {
        return "<span style='font-style:italic;'>[".$mybb->settings['regonlybbcodedeny']."]</span>";
    }
    else {
        return $groups[1];
    }
}