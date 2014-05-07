<?php
/**
 * i-net Download Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     i-net software <tools@inetsoftware.de>
 * @author     Gerry Weissbach <gweissbach@inetsoftware.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_rssbycreated extends DokuWiki_Action_Plugin {

    function register(&$controller) {
        // Support given via POST
        $controller->register_hook('FEED_ITEM_ADD', 'BEFORE', $this, 'rss_action');
        $controller->register_hook('FEED_DATA_PROCESS', 'BEFORE', $this, 'rss_action_pre');
    }
    
    function rss_action(&$event, $args) {
        global $conf;
        
        if ( $event->data['opt']['item_content'] == 'abstract' ) {
        
            $content = p_render('xhtml', p_get_instructions($event->data['item']->description), $info);
        
            // no TOC in feeds
            $content = preg_replace('/(<!-- TOC START -->).*(<!-- TOC END -->)/s','',$content);
        
            // make URLs work when canonical is not set, regexp instead of rerendering!
            if(!$conf['canonical']){
                $base = preg_quote(DOKU_REL,'/');
                $content = preg_replace('/(<a href|<img src)="('.$base.')/s','$1="'.DOKU_URL,$content);
            }
        
            $event->data['item']->description = $content;
        }
        
        $event->data['item']->date = date('r', p_get_metadata($event->data['ditem']['id'],'date created'));
        
        return true;
    }
    
    function rss_action_pre(&$event, $args) {
        global $conf;
        $event->data['rss']->title = $conf['title'];
    
        usort($event->data['data'], array($this, "__sortByDate"));
        return true;
    }
    
    function __sortByDate($a, $b) {
        $aID = $a['id'];
        $bID = $b['id'];
    
        return p_get_metadata($a['id'],'date created') <= p_get_metadata($b['id'],'date created');
    }
}