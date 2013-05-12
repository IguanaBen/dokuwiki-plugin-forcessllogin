<?php
/** 
 * @license    GPL3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Samuel Fischer <sf@notomorrow.de>
 * 
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_forcessllogin extends DokuWiki_Action_Plugin {
  function getInfo( ) {
    return array(
        'author' => 'Samuel Fischer',
        'email'  => 'sf@notomorrow.de',
        'date'   => '2012-01-23',
        'name'   => 'forcessllogin',
        'desc'   => 'redirects login requests to ssl',
        'url'    => 'http://www.dokuwiki.org/plugin:forcessllogin',
    );
  }
  function register(&$controller) {
    $controller->register_hook('TPL_ACT_RENDER', 'BEFORE',  $this, 'forcessllogin');
  }
  function forcessllogin(&$event, $param) {
    global $ACT;
    if( $ACT != 'login' && $ACT != 'register' ) return;
    if( is_ssl( )) return;

    if( !$this->getConf('splashpage')) {
      send_redirect( 'https://'.$this->host( ).'/'.DOKU_SCRIPT. '?'. $_SERVER['QUERY_STRING'] );
      exit;
    } else {

      echo $this->locale_xhtml('splashpage');
      $this->_render( $ACT );

      $event->preventDefault();
    }
  }
  function _render( $act ) {
    $form = new Doku_Form(array('id'=>'forcessllogin1',
        'action' => 'https://'.$this->host( ).'/'.DOKU_SCRIPT. '?do='.$act,
        'method' => 'get'));
    if( $this->getConf('cert')) {
      if( strpos( $this->getLang('certinfo'), '{{name}}' ) !== false ) {
        $form->addElement('<p>'
            .str_replace( '{{name}}', $this->getConf('cert'), $this->getLang('certinfo') )
            .'</p>'.NL ); } 
      else {
        $form->addElement('<p>'.$this->getLang('certinfo')." ".$this->getConf('cert').'</p>'.NL ); }}

    if( $this->getConf('ca')) {
      if( strpos( $this->getLang('ca'), '{{name}}' ) !== false ) {
        $form->addElement('<p>'
            .str_replace( '{{name}}', $this->getConf('ca'), $this->getLang('cainfo') )
            .'</p>'.NL ); } 
      else {
        $form->addElement('<p>'.$this->getLang('cainfo')
            ." <a href='".$this->getLang('cainfo')."'>".$this->getConf('ca')."</a></p>".NL ); }}

    $form->addElement(form_makeButton('submit',$act,$this->getLang('submit'),
      array('accesskey'=>'h','title'=>$this->getLang('submittitle'), id=>'focus__this' )));
    $form->printForm();

    $form = new Doku_Form(array('id'=>'forcessllogin2', 'method' => 'get'));
    $form->addElement(form_makeButton('submit','',$this->getLang('cancel'),
      array('accesskey'=>'c','title'=>$this->getLang('canceltitle'))));
    $form->printForm();
  }
  function host( ) {
    if(isset($_SERVER['HTTP_HOST'])){
        $parsed_host = parse_url('http://'.$_SERVER['HTTP_HOST']);
        $host = $parsed_host['host'];
    }elseif(isset($_SERVER['SERVER_NAME'])){
        $parsed_host = parse_url('http://'.$_SERVER['SERVER_NAME']);
        $host = $parsed_host['host'];
    }else{
        $host = php_uname('n');
    }
    return $host;
  }
}
