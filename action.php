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

    function register(Doku_Event_Handler $controller) {
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

    function handle_action_act_preprocess(&$event, $param) {
        if ($event->data !== 'log_new') {
            return;
        }
        $this->handle();
        global $ACT;
        $ACT = 'show';
    }

    private function handle() {
        try {
            log_add_log_entry($this);
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
        }
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
