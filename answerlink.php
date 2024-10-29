<?php

// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
// 
// By using this Wordpress Plug-in you acknowledge that you are not  
// being granted and are not acquiring any rights whatsoever in the  
// Service. Any use of the Service by you will be subject to and  
// governed by Answers' Corporation's Terms of Use which can be found  
// here: http://www.answers.com/main/legal_notices.jsp#terms
// 
// Answers.com provides users with access to a rich collection of  
// resources, including but not limited to, dictionaries,  
// encyclopedias, atlases, glossaries, thesauri and other reference  
// works (the "Answers.com Service") and certain software that may be  
// used in connection with the Answers.com Service, including, but not  
// limited to, 1-Click AnswersTM and AnswerTipsTM (the "Software") and  
// WikiAnswersTM, a community-based question-and-answer service using  
// the "wiki" approach of developing answers that the community  
// constantly improves (the "WikiAnswers.com Service," collectively  
// with the Answers.com Service, the WikiAnswers Service, the Sites,  
// and the Software, the "Service")
//
// This is an add-on for WordPress
// http://wordpress.org/
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// *****************************************************************

/*
Plugin Name: AnswerLinks
Description: Link to definitions and more on Answers.com from your posts.
Version: 1.1
Author: Answers.com and Crowd Favorite
Author URI: http://crowdfavorite.com
*/

global $wp_version;
if (isset($wp_version) && version_compare($wp_version, '2.5', '>=')) {
	define('AKV_25', true);
	define('AKV_25s', 'true');
}
else {
	define('AKV_25', false);
	define('AKV_25s', 'false');
}

if (!function_exists('is_admin_page')) {
	function is_admin_page() {
		if (function_exists('is_admin')) {
			return is_admin();
		}
		if (function_exists('check_admin_referer')) {
			return true;
		}
		else {
			return false;
		}
	}
}

if (is_admin_page() && !AKV_25) {
	wp_enqueue_script('jquery');
}

if (!function_exists('wp_prototype_before_jquery')) {
	function wp_prototype_before_jquery( $js_array ) {
		if ( false === $jquery = array_search( 'jquery', $js_array ) )
			return $js_array;
	
		if ( false === $prototype = array_search( 'prototype', $js_array ) )
			return $js_array;
	
		if ( $prototype < $jquery )
			return $js_array;
	
		unset($js_array[$prototype]);
	
		array_splice( $js_array, $jquery, 0, 'prototype' );
	
		return $js_array;
	}
	
	add_filter( 'print_scripts_array', 'wp_prototype_before_jquery' );
}

function cf_answerlink_header() {
	print('
<script type="text/javascript">

// var jQuery = jQuery.noConflict();
var akv_25 = '.AKV_25s.';

function al_show() {
	try {
		if (typeof document.body.style.maxHeight === "undefined") {//if IE 6
			jQuery("body","html").css({height: "100%", width: "100%"});
			jQuery("html").css("overflow","hidden");
			if (document.getElementById("TB_HideSelect") === null) {//iframe to hide select elements in ie6
				jQuery("body").append("<iframe id=\'TB_HideSelect\'></iframe><div id=\'TB_overlay\'></div><div id=\'TB_window\'></div>");
				jQuery("#TB_overlay").click(al_remove);
			}
		}else{//all others
			if(document.getElementById("TB_overlay") === null){
				jQuery("body").append(\'<div id="TB_overlay"></div><div id="TB_window"></div>\');
				jQuery("#TB_overlay").click(al_remove);
			}
		}
		
		TB_WIDTH = 700;
		TB_HEIGHT = 500;
		ajaxContentW = TB_WIDTH;
		ajaxContentH = TB_HEIGHT;
		
		al_position();
		if(frames[\'TB_iframeContent\'] === undefined){//be nice to safari
			jQuery("#TB_load").remove();
			jQuery("#TB_window").css({display:"block"});
			jQuery(document).keyup( function(e){ var key = e.keyCode; if(key == 27){al_remove();}});
		}
	
	} catch(e) {
		//nothing here
	}
}

//helper functions below
function al_remove() {
 	jQuery("#TB_imageOff").unbind("click");
	jQuery("#TB_overlay").unbind("click");
	jQuery("#TB_closeWindowButton").unbind("click");
	jQuery("#TB_window").fadeOut("fast",function(){jQuery(\'#TB_window,#TB_overlay,#TB_HideSelect\').remove();});
	jQuery("#TB_load").remove();
	if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
		jQuery("body","html").css({height: "auto", width: "auto"});
		jQuery("html").css("overflow","");
	}
	document.onkeydown = "";
	return false;
}

function al_position() {
	jQuery("#TB_window").css({marginLeft: \'-\' + parseInt((TB_WIDTH / 2),10) + \'px\', width: 700 + \'px\'});
	if ( !(jQuery.browser.msie && typeof XMLHttpRequest == \'function\')) { // take away IE6
		jQuery("#TB_window").css({marginTop: \'-\' + parseInt((500 / 2),10) + \'px\'});
	}
}

function requestAnswerLinkWindow() {
	if (document.getElementById("TB_window")) {
		return;
	}
	al_show();
	al_position();
	if (typeof tinyMCE != "undefined") {
		if (akv_25) {
			tinyMCE.triggerSave(false, false);
		}
		else {
			var inst = tinyMCE.getInstanceById("content");
			if (inst) {
				inst.triggerSave(false, false);
			}
		}
	}
	jQuery("#TB_window").css({background: "#033c66"}).append(\'<iframe name="TB_iframeContent" id="TB_iframeContent" src="'.get_bloginfo('wpurl').'/index.php?cf_action=answerlink-init" border="0" frameborder="0" style="height: 500px; width: 700px; border: 0;"></iframe>\');
	return false;
}

function addAnswerLinkTMCEButton() {
	if (!akv_25 && document.getElementById("mce_editor_0_wp_help")) {
		jQuery("#mce_editor_0_wp_help").after("<img src=\"'.get_bloginfo('wpurl').'/wp-includes/js/tinymce/themes/advanced/images/separator.gif\" class=\"mceSeparatorLine\" height=\"20\" width=\"2\" title=\"Get Suggested links from Answers.com\"><a id=\"mce_editor_0_answerlink\" href=\"'.get_bloginfo('wpurl').'/index.php\"class=\"mceButtonNormal thickbox\" target=\"_self\"><img src=\"'.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/mce_button.gif\" title=\"AnswerLinks\"></a>");
		jQuery("#mce_editor_0_answerlink").bind("click", requestAnswerLinkWindow);
	}
	else if (akv_25 && jQuery("#content_wp_adv").size()) {
		jQuery("#content_wp_adv").parent().before("<td><a title=\"AnswerLinks\" onclick=\"return false;\" onmousedown=\"return false;\" class=\"mceButton mceButtonEnabled\" href=\"javascript:;\" id=\"mce_editor_0_answerlink\"><img src=\"'.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/mce_button.gif\" class=\"mceIcon\"/></a></td>");
		jQuery("#mce_editor_0_answerlink").bind("click", requestAnswerLinkWindow);
	}
	else {
		setTimeout("addAnswerLinkTMCEButton();", 300);
	}
}

jQuery(window).load(function() {
	if (!jQuery.browser.opera) {
		if (document.getElementById("postdivrich")) {
			addAnswerLinkTMCEButton();
		}
		else {
			jQuery("#ed_close").after("<input id=\"ed_answerlink\" class=\"ed_button\" title=\"Get Suggested links from Answers.com\" value=\"AnswerLinks\" type=\"button\" />");
			jQuery("#ed_answerlink").bind("click", requestAnswerLinkWindow);
		}
	}
});

</script>
<link type="text/css" rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/thickbox.css" />
<style type="text/css">
#TB_window {
	height: 500px;
	width: 700px;
}
#TB_iframeContent {
	margin-top: 0;
}
</style>
	');
}
add_action('admin_head', 'cf_answerlink_header');

function cf_answerlink_request_handler() {
	if (!empty($_REQUEST['cf_action'])) {
		switch ($_REQUEST['cf_action']) {
			case 'answerlink-init':
				print('
				<html><body style="overflow: hidden;">
				<div style="background: url('.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/loading.gif) center center no-repeat; height: 500px; width: 700px;">
				</div>
				<form name="answerlink" id="answerlink" action="'.get_bloginfo('wpurl').'/index.php?nocache='.microtime().'" method="post">
					<input type="hidden" name="content" id="al_iframe_content" value="" />
					<input type="hidden" name="cf_action" value="answerlink-window" />
					<input type="submit" />
				</form>
				<script type="text/javascript">
				document.getElementById("al_iframe_content").value = parent.document.getElementById("content").value;
				function submitForm() {
					document.answerlink.submit();
				}
				window.onload = submitForm;
				</script>
				</body></html>
				');
				die();
				break;
			case 'answerlink-window':
				require_once(ABSPATH.WPINC.'/class-snoopy.php');
				$snoop = new Snoopy;
				$snoop->read_timeout = 5;
				$snoop->submit(
					'http://alink.answers.com/link/xml'
					, array(
						'text' => stripslashes($_POST['content'])
					)
				);
				$parser = new XMLParser;
				$parser->parse($snoop->results);
				$data = $parser->document['TEXT'][0];
				$content = $data['CONTENT'][0]['data'];
				$links = array();
				if (is_array($data['LINKS'][0]['LINK'])) {
					foreach ($data['LINKS'][0]['LINK'] as $link) {
						$links[$link['ID'][0]['data']] = array(
							'id' => $link['ID'][0]['data']
							, 'url' => $link['URL'][0]['data']
							, 'phrase' => $link['PHRASE'][0]['data']
						);
					}
				}
				@header('Content-type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
				cf_answerlink_head_html(true);
				cf_answerlink_form($content, $links);
				cf_answerlink_foot_html();
				die();
				break;
		}
	}
}
add_action('init', 'cf_answerlink_request_handler', 9999);	

function cf_answerlink_head_html($meta = false) {

if (strpos($_SERVER['HTTP_HOST'], 'wordpress.com') !== false) {
	$header_img = 'header.gif';
}
else {
	$header_img = 'header-org.gif';
}

print('
<html>
	<head>
	<style type="text/css">
body {
	color: #000;
	font-size: 12px;
	margin: 0;
	padding: 0;
	overflow: hidden;
}

#answerlinkform {
	overflow: hidden;
}
#al_header {
	background: url('.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/'.$header_img.') no-repeat;
	height: 61px;
	margin: 0;
	padding: 0;
}
#al_header span {
	display: none;
}
#al_content {
	background: url('.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/body.gif) no-repeat;
	height: 381px;
	padding: 0 15px;
	overflow: hidden;
}
#answerlinkform div.clear {
	clear: both;
	float: none;
}
#answerlinkform #answerlink_left {
	float: left;
	margin: 10px 10px 0 0;
	overflow: hidden;
	width: 450px;
}
#answerlinkform .content {
	background: #fff; 
	border: 1px solid #ccc;
	height: 260px; 
	line-height: 140%;
	margin: 0 0 10px 0;
	overflow: auto;
	padding: 7px; 
}
#answerlinkform .content a {
	color: #000;
	border-color: #000;
}
#answerlinkform .content span {
	background: #ffc;
	padding: 2px;
}
#answerlinkform .content span.active {
	background: #d9ecf5;
	border: 2px solid #95c4dc;
	padding: 2px;
}
#answerlinkform .content span.linked {
	background: #e6f8d5;
	border: 1px solid #a1dc69;
	padding: 2px;
}
#answerlinkform .content span.unlinked {
	background: #eee;
	border: 1px solid #999;
	padding: 2px;
}
#answerlinkform .content span a {
	color: blue;
	border-color: blue;
}
#answerlinkform #answerlink_links {
	float: left;
	height: 360px;
	margin-top: 10px;
	overflow: auto;
	width: 190px;
}
#answerlinkform #answerlink_links h2 {
	background: #fff;
	border: 0;
	color: #777;
	font: bold 14px Verdana Arial, Helvetica, sans-serif;
	margin: 0;
	padding: 0 0 5px 0;
}
#answerlinkform #answerlink_links ul {
	border-top: 1px solid #ccc;
	list-style: none;
}
#answerlinkform #answerlink_links ul li {
	border-bottom: 1px solid #ccc;
	padding: 5px;
}
#answerlinkform #answerlink_links ul li input {
	margin-right: 5px;
}
#al_current_phrase_wrap label {
	color: #777;
	display: block;
	float: left;
	font-size: 12px;
	font-weight: bold;
	width: 100px;
}
#al_current_phrase_wrap span.block {
	float: left;
	width: 350px;
}
#al_current_phrase_wrap span.block a {
	border: 0;
	color: blue;
	text-decoration: underline;
}
#al_current_phrase_wrap span.block span.right {
	color: #777;
	float: right;
}
#al_current_phrase {
	display: block;
	font-size: 13px;
	font-weight: bold;
}
#al_wizard {
	margin: 10px 0 0 100px;
}
#al_wizard a, #al_wizard a img {
	border: 0;
}
#answerlinkform #al_wizard_complete {
	background: #ffc;
	display: none;
	font-weight: bold;
	margin: 5px 0 0 100px;
	padding: 10px;
}
#al_footer {
	background: url('.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/footer.gif) no-repeat;
	height: 58px;
	margin: 0;
	padding: 0;
	position: relative;
	text-align: center;
}
#al_footer input, #answerlink_button {
	background:transparent url(images/fade-butt.png) repeat scroll 0%;
	border-color:#CCCCCC rgb(153, 153, 153) rgb(153, 153, 153) rgb(204, 204, 204);
	border-style:double;
	border-width:3px;
	color:#333333;
	padding:0.25em;
	margin-top: 10px;
}
#al_footer a.about {
	top: 15px;
	position: absolute;
	right: 15px;
}
#al_footer a.contact {
	bottom: 15px;
	position: absolute;
	right: 15px;
}
	</style>
	<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-includes/js/jquery/jquery.js?ver=1.1.2"></script>
	<script type="text/javascript">
var answerlink = {
	links : new Array()
	, gte25 : '.AKV_25s.'
}

answerlink.link = {
	id : ""
	, url : ""
	, phrase : ""
	, linked : false
}

answerlink.enableLink = function(id) {
	var linkData = answerlink.getLink(id);
	var phrase = linkData[1];
	var url = linkData[2];
	jQuery("#" + id).html("<a href=\"" + url + "\" class=\"answerlink\">" + phrase + "</a>").removeClass("unlinked").addClass("linked");
	jQuery("#alcb_" + id).attr("checked", "checked");
}

answerlink.disableLink = function(id) {
	var linkData = answerlink.getLink(id);
	var phrase = linkData[1];
	jQuery("#" + id).html(phrase).removeClass("linked").addClass("unlinked");
	jQuery("#alcb_" + id).removeAttr("checked");
}

answerlink.toggleLink = function(checkbox_id) {
	id = checkbox_id.replace("alcb_", "");
	document.getElementById("answerlink_content").scrollTop = document.getElementById(id).offsetTop - 80;
	answerlink.setActiveLink(id);
	if (jQuery("#" + checkbox_id).attr("checked")) {
		answerlink.enableLink(id);
	}
	else {
		answerlink.disableLink(id);
	}
}

answerlink.applyLinks = function() {
	jQuery("#answerlink_content span").each(function() {
		if (jQuery(this).attr("id") && jQuery(this).attr("id").indexOf("alnk") != -1) {
			jQuery(this).before(this.innerHTML);
			jQuery(this).remove();
		}
	});
	var content = jQuery("#answerlink_content").html();
	parent.jQuery("#content").val(content);
	if (typeof parent.tinyMCE != "undefined") {
		if (answerlink.gte25) {
			parent.tinyMCE.activeEditor.setContent(content);
		}
		else {
			var inst = parent.tinyMCE.getInstanceById("content");
			if (inst) {
				parent.tinyMCE.setContent(content);
				inst.repaint();
			}
		}
	}
	parent.al_remove();
}

answerlink.getLink = function(id) {
	for (var i = 0; i < answerlink.links.length; i++) {
		var link = answerlink.links[i];
		if (id == link[0]) {
			return [id, link[1], link[2]];
		}
	}
}

answerlink.setLink = function(action) {
	var match = false;
	for (var i = 0; i < answerlink.links.length; i++) {
		var link = answerlink.links[i];
		if (answerlink.activeLink == link[0]) {
			match = true;
			var url = link[2];
			var next = false;
			if (i + 1 < answerlink.links.length) {
				var next = answerlink.links[i + 1][0];
				var count = i + 2;
			}
			break;
		}
	}
	if (!match) {
		return;
	}
	switch (action) {
		case "enable":
			answerlink.enableLink(answerlink.activeLink);
			break;
		case "disable":
			answerlink.disableLink(answerlink.activeLink);
			break;
	}
	if (next) {
		answerlink.setActiveLink(next);
		document.getElementById("answerlink_content").scrollTop = document.getElementById(next).offsetTop - 80;
	}
	else {
		jQuery("#al_wizard").slideUp("normal", answerlink.finishedLinking);
	}
}

answerlink.setActiveLink = function(id) {
	jQuery("#answerlinkform .content span.active").removeClass("active");
	answerlink.activeLink = id;
	jQuery("#" + id).addClass("active");
	for (var i = 0; i < answerlink.links.length; i++) {
		if (id == answerlink.links[i]) {
			var count = i + 2;
			break;
		}
	}
	jQuery("#al_count").html(count);
	var linkData = answerlink.getLink(id);
	jQuery("#al_current_phrase").html(linkData[1]);
	jQuery("#al_current_link").html(linkData[2]).attr("href", linkData[2]);
}

answerlink.finishedLinking = function() {
	jQuery("#al_wizard_complete").slideDown();
}

answerlink.cancel = function() {
	parent.al_remove();
}
		</script>
	');
	if ($meta) {
?>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<?php
	}
	cf_answerlink_header();
	print('
		</head>
		<body>
	');
}

function cf_answerlink_foot_html() {
	print('
		</body>
	</html>
	');
}

function cf_answerlink_nolinks() {
	print('
<div id="answerlinkform">
	<h3 id="al_header"><span>AnswerLinks powered by <a href="http://www.answers.com" onclick="window.open(this.href); return false;">Answers.com</a></h3>
	
	<div id="al_content">
		<p style="font-size: 14px; color: #666; padding: 50px 100px; text-align: center;">Sorry, we do not have any AnswerLinks to suggest at this time.</p>
	</div>
	<p id="al_footer">
		<input type="button" name="cancel" value="&nbsp;OK&nbsp;" onclick="answerlink.cancel();" />
		<a href="http://www.answers.com/main/wordpress_howto.jsp" onclick="window.open(this.href); return false;" class="about">About This Feature</a>
		<a href="http://www.answers.com/main/contact_us.jsp" onclick="window.open(this.href); return false;" class="contact">Contact Answers.com</a>
	</p>
</div>
	');
}

function cf_answerlink_form($content = '', $links = array()) {
	if (count($links) == 0) {
		cf_answerlink_nolinks();
		return;
	}
	print('
<div id="answerlinkform">
	<h3 id="al_header"><span>AnswerLinks powered by <a href="http://www.answers.com" onclick="window.open(this.href); return false;">Answers.com</a></h3>
	
	<div id="al_content">
	');
	$content = apply_filters('the_content', $content);
	$link_js = array();
	$i = 0;
	foreach ($links as $link) {
		if ($i == 0) {
			$first_link = $link;
			$i++;
		}
		$link_js[] = '["'.$link['id'].'", "'.$link['phrase'].'", "'.$link['url'].'"]';
	}
	$link_js = '
answerlink.links = ['.implode(',', $link_js).'];
	';
	$js = str_replace('"', '\"', 'answerlink.activeLink = "'.$first_link['id'].'"; '.str_replace("\n", ' ', $link_js).' jQuery("#" + answerlink.activeLink).addClass("active");');
	
	print('

		<script type="text/javascript">
		
		answerlink.activeLink = "'.$first_link['id'].'"; '.str_replace("\n", ' ', $link_js).'
		
		</script>

		<div id="answerlink_left">

			<div class="content" id="answerlink_content">'.$content.'</div>
			
			<div class="clear"></div>
			
			<p id="al_current_phrase_wrap">
				<label for="al_current_phrase">Suggested link:</label> 
				<span class="block">
					<span class="right"><span id="al_count">1</span> of '.count($links).'</span>
					<span id="al_current_phrase">'.htmlspecialchars($first_link['phrase']).'</span>
					<a id="al_current_link" href="'.$first_link['url'].'" target="_blank">'.$first_link['url'].'</a>
				</span>
			</p>
	
			<div class="clear"></div>
			
			<p id="al_wizard">
				<a href="#" onclick="answerlink.setLink(\'enable\'); return false;"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/button_link.gif" alt="Link" /></a>
				<a href="#" onclick="answerlink.setLink(\'disable\'); return false;"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/answerlinks/images/button_dontlink.gif" alt="Don\'t Link" /></a>
			</p>
		
			<p id="al_wizard_complete">
				You\'re all done! Click OK below.
			</p>
	
		</div>
		
		<div id="answerlink_links">
			<h2>Suggestions</h2>
			<ul>
			');
			foreach ($links as $k => $v) {
				print('
				<li>
					<input type="checkbox" name="answerlink_links" id="alcb_'.$v['id'].'" onclick="answerlink.toggleLink(this.id);" />
					<label for="alcb_'.$v['id'].'" onclick="answerlink.toggleLink(this.getAttribute(\'for\'));">'.htmlspecialchars($v['phrase']).'</label>
				');
			}
			print('
			</ul>
		</div>
	
	</div>
	
	<p id="al_footer">
		<input type="button" name="ok" value="&nbsp;OK&nbsp;" onclick="answerlink.applyLinks();" />
		<input type="button" name="cancel" value="Cancel" onclick="answerlink.cancel();" />
		<a href="http://www.answers.com/main/wordpress_howto.jsp" onclick="window.open(this.href); return false;" class="about">About This Feature</a>
		<a href="http://www.answers.com/main/contact_us.jsp" onclick="window.open(this.href); return false;" class="contact">Contact Answers.com</a>
	</p>

</div>

	');
}


class XMLParser
{
	var $parser;
	var $filePath;
	var $document;
	var $currTag;
	var $tagStack;
   
	function XMLParser($path = '')
	{
	$this->parser = xml_parser_create();
	$this->filePath = $path;
	$this->document = array();
	$this->currTag =& $this->document;
	$this->tagStack = array();
	}
   
	function parse($data)
	{
		xml_set_object($this->parser, $this);
		xml_set_character_data_handler($this->parser, 'dataHandler');
		xml_set_element_handler($this->parser, 'startHandler', 'endHandler');
	   
		if(!xml_parse($this->parser, $data, true))
		{
			cf_answerlink_head_html();
			cf_answerlink_nolinks();
			cf_answerlink_foot_html();
			die();
/*
			die(
				sprintf(
					"XML error: %s at line %d"
					, xml_error_string(xml_get_error_code($this->parser))
					, xml_get_current_line_number($this->parser)
				)
			);
*/
		}
   
		xml_parser_free($this->parser);
   
		return true;
	}
   
	function startHandler($parser, $name, $attribs)
	{
		if(!isset($this->currTag[$name]))
			$this->currTag[$name] = array();
	   
		$newTag = array();
		if(!empty($attribs))
			$newTag['attr'] = $attribs;
		array_push($this->currTag[$name], $newTag);
	   
		$t =& $this->currTag[$name];
		$this->currTag =& $t[count($t)-1];
		array_push($this->tagStack, $name);
	}
   
	function dataHandler($parser, $data)
	{
		$data = trim($data);
	   
		if(!empty($data))
		{
			if(isset($this->currTag['data']))
				$this->currTag['data'] .= $data;
			else
				$this->currTag['data'] = $data;
		}
	}
   
	function endHandler($parser, $name)
	{
		$this->currTag =& $this->document;
		array_pop($this->tagStack);
	   
		for($i = 0; $i < count($this->tagStack); $i++)
		{
			$t =& $this->currTag[$this->tagStack[$i]];
			$this->currTag =& $t[count($t)-1];
		}
	}
}

?>