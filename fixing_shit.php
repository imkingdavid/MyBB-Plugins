<?php
if(!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("admin_tools_recount_rebuild_output_list", "fixing_shit_display");
$plugins->add_hook("admin_tools_recount_rebuild_start", "fixing_shit_dispatch");

function fixing_shit_info() {
    return array(
        "name"          => "Fixing Shit",
        "description"   => "Various assortment of options for fixing shit that may break on MyBB",
        "website"       => "",
        "author"        => "Matthew DeSantis",
        "authorsite"    => "http://www.anarchy46.net/",
        "version"       => "1.0",
        "guid"          => "",
        "compatibility" => "*"
    );
}
/*
function fixing_shit_install() {
}

function fixing_shit_uninstall() {
}

function fixing_shit_is_installed() {
}
*/
function fixing_shit_display() {
    global $lang;
    $form = new Form("index.php?module=tools-recount_rebuild", "post");

    $form_container = new FormContainer("Fixin' Shit");
    $form_container->output_cell("<label>Recount Attachments</label><div class=\"description\">Recounts all the attachments should the count get screwed up.</div>");
    $form_container->output_cell($form->generate_submit_button($lang->go, array("name" => "do_fix_thread_attachmentcount")));
    $form_container->construct_row();

    $form_container->end();

    $form->end();
}
function fixing_shit_dispatch() {
    if(isset($mybb->input['do_fix_thread_attachmentcount'])) {
        fix_thread_attachmentcount();

        // Log admin action
        log_admin_action("stats");
    }
}

function fix_thread_attachmentcount() {
    global $db;
    $query = $db->query("SELECT DISTINCT pid FROM ".TABLE_PREFIX."attachments");
    $threads = array();
    $posts = array();
    $count = array();
    while ($p = $db->fetch_array($query)) {
        $posts[] = $p['pid'];
    }
    $query = $db->query("SELECT tid, pid FROM ".TABLE_PREFIX."posts WHERE pid in (".implode(",", $posts).")");
    while ($t = $db->fetch_array($query)) {
        $threads[$t['tid']][] = $t['pid'];
    }
    $query = $db->query("SELECT COUNT(aid) as c, pid FROM ".TABLE_PREFIX."attachments ORDER BY pid");
    $posts = array();
    while ($ac = $db->fetch_array($query)) {
        $posts[$ac['pid']] = $ac['c'];
    }
    foreach ($threads as $tid => $thread) {
        foreach ($thread as $post) {
            if (array_key_exists($post, $posts)) {
                $count[$tid][$post] = $posts[$post];
            }
        }
    }
    foreach ($count as $key => $arr_val) $count[$key] = array_sum($arr_val);
    foreach ($count as $tid => $val) {
        $db->update_query('threads', array('attachmentcount' => $val), "tid='".intval($tid)."'");
    }
}
