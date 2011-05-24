<?php
/**
 * DokuWiki Plugin log (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Adrian Lang <lang@cosmocode.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

function log_get_log_page($plugin, $id, $needspage = false) {
    global $conf;
    if (strpos($id, $conf['start']) !== strlen($id) - strlen($conf['start'])) {
        // Force log file in ns:log for ids like ns
        $id .= ':';
    }
    $logpage = getNS($id) . ':' . $plugin->getConf('logpage');
    if (!page_exists($logpage)) {
        if (!$needspage) {
            return $logpage;
        }
        if (auth_quickaclcheck($logpage) < AUTH_CREATE) {
            throw new Exception($plugin->getLang('e_not_writable'));
        }

        global $conf;

        $locale = DOKU_PLUGIN . 'log/lang/' . $conf['lang'] . '/log.txt';
        if (!file_exists($locale)) {
            $locale = DOKU_PLUGIN . 'log/lang/en/log.txt';
        }

        $caption = useHeading('content') ? p_get_first_heading($id,true) : $id;

        saveWikiText($logpage,
                     str_replace(array('@@CAPTION@@', '@@ID@@'),
                                 array($caption, $id),
                                 file_get_contents($locale)),
                     $plugin->getLang('created_summary'));
    }
    return $logpage;
}

function log_add_log_entry($plugin) {
    global $ID;
    global $USERINFO;
    if (!checkSecurityToken() || !isset($_POST['log_text'])) {
        return;
    }
    $text = $_POST['log_text'];
    $log_id = log_get_log_page($plugin, $ID, true);
    if (auth_quickaclcheck($log_id) < AUTH_EDIT) {
        throw new Exception($plugin->getLang('e_not_writable'));
    }
    $log_text = rawWiki($log_id);
    $str = preg_split('/(\n {2,}[-*] *)/', $log_text, 2, PREG_SPLIT_DELIM_CAPTURE);
    if (count($str) === 1) {
        $str = array($log_text, "\n  * ", '');
    }
    list($pre, $lstart, $post) = $str;
    $log_text = $pre . $lstart . dformat() . ' ';
    if (!isset($_SERVER['REMOTE_USER'])) {
        $log_text .= '//' . clientIP(true) . '//';
    } else {
        if ($plugin->getConf('userpage') !== '') {
            $log_text .= '[[' . sprintf($plugin->getConf('userpage'), $_SERVER['REMOTE_USER']) . '|' . $USERINFO['name'] . ']]';
        } else {
            $log_text .= '//' . $USERINFO['name'] . '//';
        }
    }
    $log_text .= ': ' . $text;
    if ($post !== '') {
        $log_text .= $lstart . $post;
    }

    // save the log page (reset ID to avoid problems with plugins that
    // intercept IO_WIKIPAGE_WRITE but rely on $ID
    $oldid = $ID;
    $ID = $log_id;
    saveWikiText($log_id, $log_text, $plugin->getLang('summary'));
    $ID = $oldid;
}
