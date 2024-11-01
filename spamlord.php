<?php
/*
Plugin Name: SpamLord
Plugin URI: http://spamlord.org
Description: Never see your spam alive again.
Version: 0.667
Author: Steve (SD) Elliott
Author URI: http://sdelliott.com
*/

/*  Copyright 2014 Steve (SD) Elliott (email:webmaster@spamlord.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wp_version;
define('VERSION_CHECK',version_compare($wp_version,'3.0','>='));

  if(!class_exists('SpamLord')) {
  Class SpamLord { public $names,$counter,$judgement,$retort,$custom_message,$initialized,$comment_ID,$spark;

//	the "constructor"
// ===============================================================
	
	public function spamlord() {

	  if(!VERSION_CHECK) { add_action('admin_notices', array(&$this, 'wpVersionFail')); return; }
	  if(function_exists('register_activation_hook')) register_activation_hook(__FILE__, array(&$this, 'activate'));
	  if(function_exists('register_uninstall_hook')) register_uninstall_hook(__FILE__,'uninstall');

	  $this->pull_options();

	  add_action('init', array(&$this,'run_gauntlet'));
	  add_action('admin_menu', array(&$this,'add_menu_item'));
	  add_action('rightnow_end', array(&$this,'inject_stats'));
	  add_filter('comment_form_field_comment', array(&$this,'snatcher'));
	  add_action('comment_form', array(&$this,'snakepit'));
	  add_action('bbp_theme_after_topic_form', array(&$this,'snakepit'));
	  add_action('bbp_theme_after_reply_form', array(&$this,'snakepit'));
	  add_filter('comment_form_after_fields', array(&$this,'swayze'));
	  add_filter('comment_form_logged_in_after', array(&$this,'swayze'));
	  add_filter('plugin_action_links', array(&$this,'add_action_links'),9999,2);
	  add_action('wp_enqueue_scripts', array(&$this,'ninja_class'));
	  add_action('admin_enqueue_scripts', array(&$this,'ninja_class'));
	  if(function_exists('eratos')) add_filter('comment_notification_text','eratos',10,2);
	  if(function_exists('eratos')) add_filter('comment_moderation_text','eratos',10,2);
	  
	}

//	fire this sucker up
// ===============================================================
	
		public function activate() {
		  if (!get_option('spamlord')) {
			$options = array(
			  'names' => $this->generate_names(),
			  'counter' => 0, 'judgement' => 'extreme', 'retort' => 'offensive', 'custom_message' => '', 'initialized' => time(),
			  'comment_ID' => $this->random_string(), 'spark' => $this->random_string()
			); add_option('spamlord', $options); }
		  else {
			$options = get_option('spamlord');
			$options['names'] = $this->generate_names(); $options['comment_ID'] = $this->random_string(); $options['spark'] = $this->random_string();
			if(!array_key_exists('counter',$options) || empty($options['counter'])) $options['counter'] = 0;
			if(!array_key_exists('judgement',$options) || empty($options['judgement'])) $options['judgement'] = 'extreme';
			if(!array_key_exists('retort',$options) || empty($options['retort'])) $options['retort'] = 'offensive';
			if(!array_key_exists('custom_message',$options) || empty($options['custom_message'])) $options['custom_message'] = '';
			if(!array_key_exists('initialized',$options) || empty($options['initialized'])) $options['initialized'] = time();
			update_option('spamlord', $options); } }

//	the SWAYZE, good for ripping out throats
// ===============================================================

		public function swayze($fields) {
		  $fields['subject'] = '<p class="comment-form-subject ninja"><label for="subject">'.__('Subject').'</label><input id="subject" name="subject" type="text" size="30" tabindex="1" autofill="off" value="" /></p>';
		  return $fields; }
		
//	the SNATCHER, a deadly foe
// ===============================================================
	
		public function snatcher($field) {
		  if(!empty($this->comment_ID)) {
			$snatched = preg_replace(
			  "#<textarea(.*?)name=([\"\'])comment([\"\'])(.+?)</textarea>#s","<textarea$1name=$2comment-".$this->comment_ID."$3$4</textarea><textarea name=\"comment\" rows=\"1\" cols=\"1\" class=\"ninja\"></textarea>",$field, 1);
			if(strcmp($field,$snatched)) $snatched .= '<input type="hidden" name="comment-snatched" value="true" />'; return $snatched;
		  } else return $field; }
		  
//	the SNAKEPIT, a devious trap
// ===============================================================
	
		public function snakepit() {
		  $time = time(); $spamo = $this->names; print '<p class="comment-form-meta ninja"><input type="text" name="stamped" value="'.$time.'" /><input type="text" name="sealed" value="'.sha1($time.$this->spark).'" />';
		  if(rand(1,2)==1) print '<input type="text" name="'.$spamo['uno'].'" value="" /><input type="text" name="'.$spamo['dos'].'" value="'.$spamo['dosx'].'" />'; else print '<input type="text" name="'.$spamo['dos'].'" value="'.$spamo['dosx'].'" /><input type="text" name="'.$spamo['uno'].'" value="" />'; print '</p>'; }
		  
//	the ancient NINJA CLASS is hidden by the shadows...
// ===============================================================
	
		public function ninja_class() {
		  wp_register_style('spamlord_ninja', plugins_url('spamlord_ninja.css',__FILE__), false, '1.1', 'all'); wp_enqueue_style('spamlord_ninja'); }
			
//	go through SpamLord's various security checks
// ===============================================================
	
		public function run_gauntlet() {
		  if(basename($_SERVER['PHP_SELF']) == 'wp-comments-post.php' || (isset($_POST['action']) && ($_POST['action'] == 'bbp-new-topic' || $_POST['action'] == 'bbp-new-reply'))) {
			// check the bouncer, aka swayze
			  if(!empty($_POST['subject'])) $this->guillotine();
		    // check the snatcher
			  if(isset($_POST['comment-snatched'])) { $snatchee = $_POST['comment']; $snatcher = $_POST['comment-'.$this->comment_ID]; if(empty($snatchee) && !empty($snatcher)) $_POST['comment'] = $snatcher; else $this->guillotine(); }
			// rearrange bbPress vars (just in case)
			  $author = !empty($_POST['bbp_anonymous_name']) ? $_POST['bbp_anonymous_name'] : $_POST['author']; $email = !empty($_POST['bbp_anonymous_email']) ? $_POST['bbp_anonymous_email'] : $_POST['email']; $url = !empty($_POST['bbp_anonymous_website']) ? $_POST['bbp_anonymous_website'] : $_POST['url']; $comment = !empty($_POST['bbp_topic_content']) ? $_POST['bbp_topic_content'] : $_POST['comment']; $comment = !empty($_POST['bbp_reply_content']) ? $_POST['bbp_reply_content'] : $comment;
			// check the snakepit
			  $spamo = $this->names; if($you == 'owe me money') $this->guillotine(); else if(!array_key_exists($spamo['uno'],$_POST)) $this->guillotine(); else if($_POST[$spamo['uno']] != "") $this->guillotine(); else if(!array_key_exists($spamo['dos'],$_POST)) $this->guillotine(); else if($_POST[$spamo['dos']] != $spamo['dosx']) $this->guillotine(); else if(!array_key_exists('stamped',$_POST) || !array_key_exists('sealed',$_POST)) $this->guillotine(); else if (sha1($_POST['stamped'] . $this->spark) != $_POST['sealed']) $this->guillotine(); else if (time() < $_POST['stamped']+10) $this->toofast(); }}
		
//	for when someone is commenting too fast
// ===============================================================
	
		public function toofast() { $this->counter++; $this->push_options(); $message = "<p>".$this->get_message()."</p><p><a href='javascript:history.back()'>Back</a></p>"; wp_die($message, '', array('response' => 403)); } // esperanto for "suck butter from my ass"

//	meet the executioner
// ===============================================================
	
		public function guillotine() {
		  if ($this->judgement=='moderate') add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'));
		  else {
			$this->counter++; $this->push_options();
			$message = "<p>".$this->get_message()."</p><p><a href='javascript:history.back()'>Back</a></p>"; 
			wp_die($message, '', array('response' => 403)); }}
			
//	ERATOS enhances your record keeping with advanced IP information
// =============================================================================
	public function eratos($str,$comment_id) { 

		// ip detection
			$ip = $_SERVER['REMOTE_ADDR']; $ipBlock = explode('.',$ip); $ipProxyVIA = $_SERVER['HTTP_VIA'];
			$MaskedIP = $_SERVER['HTTP_X_FORWARDED_FOR']; // Stated Original IP - Can be faked
			$MaskedIPBlock = explode('.',$MaskedIP);
			if(eregi("^([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])",$MaskedIP)&&$MaskedIP!=""&&$MaskedIP!="unknown"&&!eregi("^192.168.",$MaskedIP)) { $MaskedIPValid = true; $MaskedIPCore = rtrim($MaskedIP,' unknown;,'); }
			if(!$MaskedIP) $MaskedIP='[no data]';
			$ReverseDNS = gethostbyaddr($ip); $ReverseDNSIP = gethostbyname($ReverseDNS);
			if($ReverseDNSIP!=$ip||$ip==$ReverseDNS) $ReverseDNSAuthenticity = '[Possibly Forged]';
			else $ReverseDNSAuthenticity = '[Verified]';
		
		// detect proxy use
			if($_SERVER['HTTP_VIA']||$_SERVER['HTTP_X_FORWARDED_FOR']) { $ipProxy = 'PROXY DETECTED'; $ipProxyShort = 'PROXY'; $ipProxyData = $ip.' | MASKED IP: '.$MaskedIP; $ProxyStatus = 'TRUE'; } 
			else { $ipProxy='No Proxy'; $ipProxyShort=$ipProxy; $ipProxyData=$ip; $ProxyStatus='FALSE'; }

		// add text
			$str.= "\n".'-------------------------------';
			$str.= "\n".' SpamLord Additional Records';
			$str.= "\n".'-------------------------------';
			$str.= "\n\t".'Comment Processor Referrer: '.$_SERVER['HTTP_REFERER'];
			$str.= "\n\t".'User-Agent: '.$_SERVER['HTTP_USER_AGENT'];
			$str.= "\n\n\t".'- Advanced Info ---';
			$str.= "\n\t\t".$ip.' :IP Address';
			$str.= "\n\t\t".$_SERVER['REMOTE_HOST'].' :Remote Host';
			$str.= "\n\t\t".$ReverseDNS.' :Reverse DNS';
			$str.= "\n\t\t".$ReverseDNSIP.' :Reverse DNS IP';
			$str.= "\n\t\t".$ReverseDNSAuthenticity.' :Reverse DNS Authenticity';
			$str.= "\n\t\t".$ipProxy.' :Proxy Info';
			$str.= "\n\t\t".$ipProxyData.' :Proxy Data';
			$str.= "\n\t\t".$ProxyStatus.' :Proxy Status';
			if($_SERVER['HTTP_VIA']) $str.= "\n\t\t".$_SERVER['HTTP_VIA'].' :HTTP_VIA';
			if ($_SERVER['HTTP_X_FORWARDED_FOR']) $str.= "\n\t\t".$_SERVER['HTTP_X_FORWARDED_FOR'].' :HTTP_X_FORWARDED_FOR';
			$str.= "\n\t\t".$_SERVER['HTTP_ACCEPT_LANGUAGE'].' :HTTP_ACCEPT_LANGUAGE';
			$str.= "\n\t\t".$_SERVER['HTTP_ACCEPT'].' :HTTP_ACCEPT';
			$str.= "\n\n\t".'Lookup: http://www.dnsstuff.com/tools/ipall/?ip='.$ip;
			return $str; }

//	assorted utility functions vital to the health of SpamLord
// ===============================================================
	
		public function pull_options() {
		  $options = get_option('spamlord');
		  $this->names = $options['names']; $this->counter = $options['counter']; $this->judgement = $options['judgement']; $this->retort = $options['retort']; $this->initialized = $options['initialized']; $this->comment_ID = $options['comment_ID']; $this->spark = $options['spark'];
		  $this->messages = array();
		  if($this->retort=='custom') {
			$this->messages[] = $options['custom_message'];
		  } else if($this->retort=='sneaky') {
			$this->messages[] = "Success!";
		  } else {
			$lang = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
			$this->messages = $this->get_messages_in_language($lang);
		  }
		}
		
		public function get_messages_in_language($lang="oo") {
			$abbr = !empty($lang) ? substr($lang,0,2) : "oo";
			switch ($abbr) {
			 case 'en': $messages = array("You are a wonderful person.","Have you done something with your hair?","That is the greatest comment ever.","You do not look fat in those pants.","How interesting!","You make my dreams come true!","Thanks for checking in!","Where have you been all my life?","How thoughtful.","We should be friends."); break;
			 case 'zh': $messages = array("Ni shìgè liaobùqi de rén.","Ni zuòle shénme ni de tóufà?","Zhè shì zuìdà de yìjiàn ba.","Ni bié kàn zhifáng zài nà tiáo kùzi.","Zhen youqù!","Ni ràng wo de mèngxiang chéng zhen!","Xièxiè ni jianchá!","You ni qù na'erle wo suoyou de shenghuó?","Rúhé zhoudào.","Women yinggai shì péngyou."); break;
			 case 'ru': $messages = array("Vy zamechatel'nyy chelovek.","Vy sdelali chto-to s tvoimi volosami?","Eto velichayshaya kommentariy kogda-libo.","Vy ne polnit v etikh shtanakh.","Kak interesno!","Vy delayete moi mechty sbyvayutsya!","Spasibo za proverku v!","Gde ty byl vsyu moyu zhizn'?","Kak vdumchivyy.","My dolzhny byt' druz'yami."); break;
			 case 'ja': $messages = array("Anata wa subarashi hitodesu.","Anata wa, anata no kami de nanika o yatta koto ga arimasu ka?","Sore wa saidai no komento wa kore madedesu.","Anata wa, korera no zubon no shibo miteinai.","Do no yo ni omoshiroi!","Anata wa watashinoyume o kanaeru!","Ni chekku shite kurete arigato!","Anata wa doko ni subete no watashinojinsei sa rete kita?","Dono yo ni shiryobukai.","Wareware wa yujindenakereba narimasen."); break;
			 case 'uk': $messages = array("Vy chudova lyudyna.","Vy zrobyly shchos' z vashymy volossyam?","Tse naybil'sha komentar bud'-koly.","Vy ne povnyt' v tsykh shtanakh.","Yak tsikavo!","Vy robyte moyi mriyi zbuvayut'sya!","Spasybi za perevirku v!","De ty buv vse moye zhyttya?","Yak vdumlyvyy.","My povynni buty druzyamy."); break;
			 case 'de': $messages = array("Du bist ein wunderbarer Mensch.","Haben Sie etwas mit Ihren Haaren gemacht?","Das ist die größte Kommentar überhaupt.","Du musst nicht fett aussehen in diesen Hosen.","Wie interessant!","Du machst meine Träume wahr werden!","Vielen Dank für dein rein!","Wo warst du mein ganzes Leben gewesen?","Wie nett.","Wir sollten Freunde sein."); break;
			 case 'pt': $messages = array("Você é uma pessoa maravilhosa.","Você já fez alguma coisa com o seu cabelo?","Esse é o maior comentário que nunca.","Você não olha a gordura nas calças.","Que interessante!","Você faz meus sonhos!","Obrigado por check-in!","Onde você esteve toda a minha vida?","Como pensativo.","Devemos ser amigos."); break;
			 case 'hi': $messages = array("Tuhanu ika sanadara vi'akati nu hai.","Tuhanu apa?e vala de nala kama kita hai?","Iha sabha?ipa?i kade vi hai.","Tuhanu uha pa?a vica carabi nu vekha na karo.","Kine dilacasapa!","Tu mere supane sace ho!","Vica lagi'a la'i dhanavada hai!","Tu kithe mere sare jivana nu kita gi'a hai?","Kisa soca.","Sanu dosata ho?a cahida hai."); break;
			 case 'fr': $messages = array("Vous êtes une personne merveilleuse.","Avez-vous fait quelque chose avec vos cheveux?","Ce sera le plus grand jamais commentaire.","Vous ne regardez pas la graisse dans les pantalons.","Comment intéressant!","Vous faites mes rêves!","Merci pour le check-in!","Où avez-vous été toute ma vie?","Comment réfléchie.","Nous devrions être amis."); break;
			 case 'es': $messages = array("Usted es una persona maravillosa.","¿Ha hecho algo con tu pelo?","Ese es el mayor comentario nunca.","No te ves gorda en esos pantalones.","¡Qué interesante!","Haces que mis sueños se hagan realidad!","Gracias por comprobar en!","¿Dónde has estado toda mi vida?","Que bien.","Debemos ser amigos."); break;
			 case 'sw': $messages = array("Wewe ni mtu wa ajabu.","Je amefanya kitu kwa nywele yako?","Hiyo ni maoni mkubwa milele.","Huwezi kuangalia mafuta katika suruali hizo.","Jinsi ya kuvutia!","Unaweza kufanya ndoto yangu kuja kweli!","Shukrani kwa ajili ya kuangalia katika!","Ambapo umekuwa maisha yangu yote?","Jinsi ya wasiwasi.","Tunapaswa kuwa marafiki."); break;
			 case 'ko': $messages = array("dangsin-eun meosjin salam ibnida.","dangsin-eun dangsin-ui meoli e mwongaleul jis-eul hangeoya?","geugeon gajang keun juseog jeog ida.","dangsin-eun geu baji e jibang boji anhneunda.","eotteohge jaemi!","dangsin-eun nae kkum eul ilwo jul!","e chekeuleul-wihan gamsahabnida!","eodi nae insaeng iss-eossnayo?","eotteohge salyeo gip-eun.","ulineun chinguga doel su iss-eoyahabnida."); break;
			 case 'sv': $messages = array("Du är en underbar person.","Har du gjort något med ditt hår?","Det är den största kommentaren någonsin.","Du ser inte fett i de där byxorna.","Så intressant!","Du gör mina drömmar!","Tack för att du checkar in!","Var har du varit hela mitt liv?","Så omtänksamt.", "Vi borde vara vänner."); break;
			 default: $messages = array("Vi estas mirinda persono.","Cu vi faris ion per viaj haroj?","Kiu estas la plej granda komenton iam.","Vi ne aspektas graso en tiuj pantalonoj.","Kiel interese!","Vi faras miajn songojn!","Dankon por kontroli la!","Kie vi estis mia vivo?","Kiom pensema.","Ni devus esti amikoj."); break; }
			return $messages; }
		
		
		
		public function get_message() { return $this->messages[rand(0,count($this->messages)-1)]; }
		  
		public function push_options() {
		  $options = array('names' => $this->names, 'counter' => $this->counter, 'judgement' => $this->judgement, 'retort' => $this->retort, 'custom_message' => $this->custom_message, 'initialized' => $this->initialized, 'comment_ID' => $this->comment_ID, 'spark' => $this->spark);
		  update_option('spamlord',$options); }
		  
		public function stats_per_day() {
		  $secs = time() - $this->initialized; $days = ($secs / (24*3600)); ($days <= 1) ? $days = 1 : $days = floor($days);
		  return ceil($this->counter / $days); }
		  
		public function show_stats($dashboard=false) {
		  if(function_exists('__ngettext')) {
			if($dashboard) echo "<p>";
			$mylabel = $dashboard ? '<a href="'.admin_url('options-general.php?page=spamlord').'">'.__('SpamLord').'</a>' : __('SpamLord');
			if($this->counter<=0) echo __("SpamLord has yet to be challenged, but remains hopeful.", 'spamlord');
			else {
			  printf(
				_n("Since %s %s has executed %s spam attempts (approx. %s per day).","Since %s %s has executed %s spam attempts (approx. %s per day).",$this->counter,'spamlord'),date_i18n(get_option('date_format'),$this->initialized),$mylabel,$this->counter,$this->stats_per_day());
			} if ($dashboard) echo "</p>"; }}
		public function inject_stats() { $this->show_stats(true); }
		public function check_nonce($nonce) { if(!wp_verify_nonce($nonce,'el-nonce')) wp_die(__($this->get_message(),'spamlord'), '', array('response' => 403)); return true; } 
		public function checkIP($ip,$cidr) { list ($net,$mask) = split ("/",$cidr); $ip_net = ip2long($net); $ip_mask = ~((1<<(32-$mask))-1); $ip_ip = ip2long($ip); $ip_ip_net = $ip_ip & $ip_mask; if($ip_ip_net == $ip_net) return 1; else return 0; }
		public function checkCIDR($word) { return preg_match("^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/(\d|[1-2]\d|3[0-2]))$^",$word); }
		public function random_string() { return substr(sha1(uniqid(rand(), true)), rand(8, 32)); }
		public function generate_names() { $spamo = array( 'uno' => $this->random_string(), 'dos' => $this->random_string(), 'dos_v' => $this->random_string() ); return $spamo; }
		public function display_message($message,$class='updated') { echo "<div id='message' class='".$class."'><p>".$message."</p></div>"; }
		public function wpVersionFail() { $this->displayError(__('WordPress 3.0+ is required to take advantage of SpamLord\'s extreme spam fighting abilities.','spamlord')); }
		
//	add a little "configure" link on the plugin page
// ===============================================================
	
		public function add_action_links($links,$file) {
		  if($file == 'spamlord/spamlord.php' && function_exists("admin_url")) {
			$settings_link = '<a href="'.admin_url('options-general.php?page=spamlord').'">'.__('Configure').'</a>'; array_push($links, $settings_link);
		  } return $links; }

//	stick it on the admin menu
// ===============================================================		  
		  
		public function add_menu_item() { add_options_page('SpamLord Configuration','SpamLord','manage_options','spamlord',array(&$this,'config_page')); }
		
//	outputs the configuration page
// ===============================================================
			
		public function config_page() { if(!current_user_can('manage_options')) wp_die(__('You are not important enough to alter SpamLord\'s configuration. Begone, Scallywag!','spamlord'),'',array('response'=>403));

		  global $wpdb; 
		
		  (isset($_REQUEST['_wpnonce'])) ? $nonce = $_REQUEST['_wpnonce'] : $nonce = '';
		  (isset($_POST['save_settings'])) ? $save_settings = $_POST['save_settings'] : $save_settings = '';
		  (isset($_POST['reset_counter'])) ? $reset_counter = $_POST['reset_counter'] : $reset_counter = '';
		  
		  if($save_settings == 1 && $this->check_nonce($nonce)) {

		    if(isset($_POST['spamlord_mode'])) {
		  
				switch($_POST['judgement']) { // extreme judgement must also set all posts of all types to no longer accept pings
				  case 'extreme': $this->judgement = 'extreme'; add_filter('wp_xmlrpc_server_class','maxmillian'); maximillian_db(); break;
				  case 'severe': $this->judgement = 'severe'; break;
				  case 'moderate': $this->judgement = 'moderate'; break;
				  default: $this->judgement = 'moderate'; }
				$this->push_options(); // save
				$this->display_message(__('Updated SpamLord\'s Operating Mode!','spamlord'));
				
			} else if(isset($_POST['spamlord_language'])) {
				$this->custom_message = "";
				switch($_POST['retort']) { // language level
				  case 'offensive': $this->retort = 'offensive'; break;
				  case 'sneaky': $this->retort = 'sneaky'; break;
				  case 'custom': $this->retort = 'custom'; $this->custom_message = $_POST['custom_message']; break;
				  default: $this->retort = 'sneaky'; }
				$this->push_options(); // save them to the database
				$this->display_message(__('Updated SpamLord\'s Language Level!','spamlord'));
			}
			
		  } else if ($reset_counter == 1 && $this->check_nonce($nonce)) {
				$this->counter = 0; $this->initialized = time(); $this->push_options();
				$this->display_message(__('SpamLord counter reset!','spamlord'));
		  }
		  
		  $judgement['moderate'] = ''; $judgement['severe'] = ''; $judgement['extreme'] = '';
		  switch ($this->judgement) {
			case 'extreme': $judgement['extreme'] = 'checked'; break;
			case 'severe': $judgement['severe'] = 'checked'; break;
			case 'moderate': $judgement['moderate'] = 'checked'; break;
			default: $judgement['moderate'] = 'checked'; }
		  $retort['offensive'] = ''; $retort['sneaky'] = ''; $retort['custom'] = ''; $retort['custom_message'] = '';
		  switch ($this->retort) {
			case 'offensive': $retort['offensive'] = 'checked'; break;
			case 'sneaky': $retort['sneaky'] = 'checked'; break;
			case 'custom': $retort['custom'] = 'checked'; $retort['custom_message'] = $this->custom_message; break;
			default: $retort['sneaky'] = 'checked'; }
		  $confirm = __('Are you sure you want to reset the counter?','spamlord');
		  $nonce = wp_create_nonce('el-nonce');
		  
		  print '
		    <div class="wrap spamlord">
			
			  <div id="icon-spamlord" class="icon32"></div>
			  <h2>'.__('SpamLord Configuration','spamlord').'</h2>
			  
			  <div id="poststuff">
			  
				  <div class="postbox opened"><h3>'.__('Operating Mode','spamlord').'</h3><div class="inside">
					  <p>'.__('SpamLord\'s recommended mode is "Total Annihilation". In this mode, SpamLord could care less about appearances. All comments determined to be spam are executed with extreme prejudice, and all trackbacks and pingbacks are snuffed out without mercy. In other words, <strong>there are no second chances</strong>.','spamlord').'</p>
					  <h4>'.__('The following alternatives are here to solely to placate Spam Rights Activists, and are not at all recommended.','spamlord').'</h4>
					  <form action="options-general.php?page=spamlord&_wpnonce='.$nonce.'" method="post">
						<table class="form-table"><tr><th scope="row" valign="top"><strong>'.__('Operating Mode','spamlord').'</strong></th>
						  <td><input type="hidden" value="true" name="spamlord_mode">
							<input type="radio" '.$judgement['extreme'].' name="judgement" value="extreme"> '.__('Total Annihilation (reject spam, Trackbacks disabled)','spamlord').'<br>
							<input type="radio" '.$judgement['severe'].' name="judgement" value="severe"> '.__('Severe (reject spam, Trackbacks enabled)','spamlord').'<br>
							<input type="radio" '.$judgement['moderate'].' name="judgement" value="moderate"> '.__('Moderate (flag spam, Trackbacks enabled)','spamlord').'
						  </td></tr></table><input type="hidden" value="1" name="save_settings"><p><input name="submit" class="button-primary" value="'.__('Save Operating Mode','spamlord').'" type="submit"></p>
					  </form></div></div>
					  
				  <div class="postbox opened"><h3>'.__('Language Level','spamlord').'</h3><div class="inside">
					  <p>'.__('SpamLord\'s recommended language level is "Tastefully Sarcastic". At this level, SpamLord attempts to offend spammers with an <strong>arsenal of tasteful sarcastic responses</strong>. In honor of my Great Grandmother, these phrases have been translated into Esperanto.','spamlord').'</p>
					  <h4>'.__('If you have a particularly strong moral fiber, however, and are uncomfortable teasing the worst scum on Earth, then perhaps you might find one of the other settings more to your liking.','spamlord').'</h4>
					  <form action="options-general.php?page=spamlord&_wpnonce='.$nonce.'" method="post">
						<table class="form-table"><tr><th scope="row" valign="top"><strong>'.__('Language Level','spamlord').'</strong></th>
						  <td><input type="hidden" value="true" name="spamlord_language">
							<input type="radio" '.$retort['offensive'].' name="retort" value="offensive" onclick="javascript:document.getElementById(\'custom_message_input\').className = \'ninja\';"> '.__('Tastefully Sarcastic (recommended)','spamlord').'<br>
							<input type="radio" '.$retort['sneaky'].' name="retort" value="sneaky" onclick="javascript:document.getElementById(\'custom_message_input\').className = \'ninja\';"> '.__('False Positive (sneaky)','spamlord').'<br>
							<input type="radio" '.$retort['custom'].' name="retort" value="custom" onclick="javascript:document.getElementById(\'custom_message_input\').className = \'\';"> '.__('Custom Message','spamlord').'
						  </td></tr><tr id="custom_message_input" class="'; if(!$retort['custom_message']) print 'ninja'; print '"><th scope="row" valign="top"><strong>'.__('Your Message to Spammers','spamlord').'</strong></th><td>
							<textarea name="custom_message" cols="64" rows="6">'.$retort['custom_message'].'</textarea> 
						  </td></tr></table><input type="hidden" value="1" name="save_settings"><p><input name="submit" class="button-primary" value="'.__('Save Language Level','spamlord').'" type="submit"></p>
					  </form></div></div>
					  
				  <div class="postbox opened"><h3>'.__('Statistics','spamlord').'</h3><div class="inside">
				 	<form action="options-general.php?page=spamlord&_wpnonce='.$nonce.'" method="post" onclick="return confirm(\''.$confirm.'\');">
					  <p class="statistics">'; $this->show_stats(); print '</p>
					  <p><input type="hidden" value="1" name="reset_counter"><input name="submit" class="button-primary" value="'.__('Reset Counter','spamlord').'" type="submit"></p>
					</form></div></div>
				
			</div></div>'; }

//	get RID of it? why?? no accounting for taste...
// ===============================================================
	
		static function uninstall() {
		  delete_option('spamlord'); }

// ===============================================================
// ===============================================================
// ===============================================================
		
	} $spamlord = new SpamLord();
}

//	MAXIMILLIAN, a murderous red button for trackbacks and pingbacks
// =============================================================================
	function maxmillian() { return 'xmlrpc_killer'; } // maximillian activates on the extreme setting
	require_once(ABSPATH.WPINC.'/class-IXR.php'); class xmlrpc_killer extends IXR_Server {
		function __construct() { $this->methods = array('pingback.ping' => 'this:pingback_ping','pingback.extensions.getPingbacks' => 'this:pingback_extensions_getPingbacks','demo.sayHello' => 'this:sayHello','demo.addTwoNumbers' => 'this:addTwoNumbers'); $this->initialise_blog_option_info(); $this->methods = apply_filters('xmlrpc_methods', $this->methods); }
		function serve_request() { } function initialise_blog_option_info() { global $wp_version; $this->blog_options = array( ); } }
	function maximillian_db() { global $wpdb; $wpdb->query('UPDATE '.$wpdb->prefix.'posts SET ping_status="closed";'); $wpdb->query('UPDATE '.$wpdb->prefix.'options SET option_value="closed" WHERE option_name="default_ping_status";'); }
	