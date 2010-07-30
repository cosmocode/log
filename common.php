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
