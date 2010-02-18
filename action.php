<?php
/**
 * DokuWiki Plugin log (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Adrian Lang <lang@cosmocode.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'log/common.php';
require_once DOKU_PLUGIN.'action.php';

class action_plugin_log extends DokuWiki_Action_Plugin {

    function register(&$controller) {
       $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call_unknown');
       $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
       $controller->register_hook('PARSER_CACHE_USE','BEFORE', $this, 'handle_cache_prepare');
    }

    function handle_cache_prepare(&$event, $param) {
        $cache =& $event->data;

        // we're only interested in wiki pages
        if (!isset($cache->page)) return;

        // get meta data
        if (!p_get_metadata($cache->page, 'relation logplugin')) {
            // No log used
            return;
        }

        $cache->depends['files'][] = wikiFN(log_get_log_page($this, $cache->page));
    }

    function handle_ajax_call_unknown(&$event, $param) {
    }

    function handle_action_act_preprocess(&$event, $param) {
        if ($event->data !== 'log_new') {
            return;
        }
        $event->preventDefault();
        $this->handle();
        global $ACT;
        $ACT = 'show';
    }

    private function handle() {
        global $ID;
        global $USERINFO;
        if (!checkSecurityToken() || !isset($_POST['log_text'])) {
            return;
        }
        $text = $_POST['log_text'];
        $log_id = log_get_log_page($this, $ID);
        if (auth_quickaclcheck($log_id) < AUTH_EDIT) {
            msg($this->getLang('e_not_writable'), -1);
        }
        $log_text = rawWiki($log_id);
        $str = preg_split('/(\n {2,}[-*] *)/', $log_text, 2, PREG_SPLIT_DELIM_CAPTURE);
        if (count($str) === 1) {
            $str = array($log_text, "\n  *", '');
        }
        list($pre, $lstart, $post) = $str;
        $log_text = $pre . $lstart . strftime('%Y-%m-%d %R ');
        if (!isset($_SERVER['REMOTE_USER'])) {
            $log_text .= '//' . clientIP(true) . '//';
        } else {
            if ($this->getConf('userns') !== '') {
                $log_text .= '[[' . sprintf($this->getConf('userpage'), $USERINFO['name']) . '|' . $USERINFO['name'] . ']]';
            } else {
                $log_text .= '//' . $USERINFO['name'] . '//';
            }
        }
        $log_text .= ': ' . $text;
        if ($post !== '') {
            $log_text .= $lstart . $post;
        }
        saveWikiText($log_id, $log_text, $this->getLang('summary'));
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
