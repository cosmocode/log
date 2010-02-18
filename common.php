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

function log_get_log_page($plugin, $id) {
    $logpage = curNS($id) . ':' . $plugin->getConf('logpage');
    if (!page_exists($logpage)) {
        return $this->getLang('e_nolog');
    }
    return $logpage;
}
