<?php

require_once 'common.php';

function log_handle_ajax() {
    global $AJAX_JSON;
    global $ID;
    $ID = cleanID($_POST['id']);
    if (isset($_POST['maxcount'])) {
        $maxcount = (int) $_POST['maxcount'];
    } else {
        $maxcount = 5;
    }
    try {
        log_add_log_entry(new action_plugin_log);
    } catch (Exception $e) {
        header('HTTP/1.0 500 Internal Server Error');
        echo $e->getMessage();
        return;
    }

    $info = array();
    echo p_render('xhtml', p_get_instructions('{{log>' . $maxcount . '}}'), $info);
}

log_handle_ajax();
