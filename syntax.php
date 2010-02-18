<?php
/**
 * DokuWiki Plugin log (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Adrian Lang <lang@cosmocode.de>
 */

require_once DOKU_PLUGIN.'log/common.php';
require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_log extends DokuWiki_Syntax_Plugin {

    function getType() {
        return 'substition';
    }

    function getPType() {
        return 'block';
    }

    function getSort() {
        return 55;
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{log(?:>[^}]+)?}}',$mode,'plugin_log');
    }

    function handle($match, $state, $pos, &$handler){
        global $ID;

        if (preg_match('/{{log(?:>(\d+))?}}/', $match, $match) === 0) {
            return $this->getLang('e_syntax');
        }
        $maxcount = count($match) > 1 ? $match[1] : 5;

        try {
            $logpage = log_get_log_page($this, $ID);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $instructions = p_cached_instructions(wikiFN($logpage),false,$logpage);

        // prepare some vars
        $max = count($instructions);
        $start = -1;
        $end = -1;
        $lvl = 0;

        // build a lookup table
        for($i=0; $i<$max; $i++){
            switch ($instructions[$i][0]) {
            case 'listu_open': case 'listo_open':
                if ($lvl === 0) {
                    $start = $i + 1;
                    $type = substr($instructions[$i][0], 4, 1);
                }
                ++$lvl;
                break;
            case 'listitem_close':
                if ($lvl === 1 && --$maxcount === 0) {
                    $instructions = array_slice($instructions, $start, $i - $start + 1);
                    break 2;
                }
                break;
            case 'listu_close': case 'listo_close':
                if (--$lvl === 0) {
                    $instructions = array_slice($instructions, $start, $i - $start + 1);
                    break 2;
                }
            }
        }
        if ($start === -1) {
            return $this->getLang('e_invalidlog');
        }

        return array(array_merge($instructions,
                                 array(array('listitem_open', array(1)),
                                       array('listcontent_open'),
                                       array('internallink', array($logpage, $this->getLang('fulllog'))),
                                       array('listcontent_close'),
                                       array('listitem_close'))),
                     $type);
    }

    function render($mode, &$renderer, $data) {
        if($mode === 'metadata'){
            $renderer->meta['relation']['logplugin'] = true;
            return true;
        }

        if($mode !== 'xhtml') return false;

        if (!is_array($data)) {
            // Show error
            $renderer->doc .= hsc($data);
            return true;
        }

        global $ID;

        call_user_func(array(&$renderer, 'list' . $data[1] . '_open'));

        call_user_func(array(&$renderer, 'listitem_open'), 1);
        call_user_func(array(&$renderer, 'listcontent_open'));
        $form = new Doku_Form($ID, wl($ID,array('do'=>'log_new'),false,'&'));
        $form->addElement(form_makeTextField('log_text', '', $this->getLang('newentry'), 'log__nt', 'edit'));
        $form->addHidden('id', $ID);
        $form->addElement(form_makeButton('submit', null, $this->getLang('save')));

        $renderer->doc .= $form->getForm();
        call_user_func(array(&$renderer, 'listcontent_close'));
        call_user_func(array(&$renderer, 'listitem_close'));

        foreach ($data[0] as $instruction ) {
            // Execute the callback against the Renderer
            call_user_func_array(array(&$renderer, $instruction[0]),$instruction[1]);
        }

        call_user_func(array(&$renderer, 'list' . $data[1] . '_close'));
        return true;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
