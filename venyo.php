<?php
  /*
  Plugin Name: venyo
  Plugin Script: venyo.php
  Plugin URI: http://www.venyo.org
  Description: Manage your online reputation 
  Version: 1.0
  Author: Fabrice Cornet
  Author URI: http://fabrice.venyo.org
  
  === RELEASE NOTES ===
  2009-03-24 - v1.0 - first version
  */
  
  register_activation_hook(__FILE__,'venyo_install');
  function venyo_install()
  {
    add_option('venyo_partner_name', '','',yes);
    add_option('venyo_passphrase','','',yes);
    add_option('venyo_cryptography','md5','',yes);
    add_option('venyo_tags','','',yes);
    add_option('venyo_language','en','',yes);
    add_option('venyo_context',1,'',yes);
  }
  
	add_action('admin_menu', 'venyo_plugin_menu');  
  function venyo_plugin_menu()
  {
    add_submenu_page('options-general.php', 'Venyo Reputation Plugin Config Page', 'Venyo Reputation Configuration', 10, __FILE__, 'venyo_config_plugin');   
  }

  function venyo_config_plugin()
  {
    if(isset($_POST['submitted']))
    {
      //Update plugin options
      update_option("venyo_partner_name", trim($_POST['venyo_partner_name']));
      update_option("venyo_passphrase", trim($_POST['venyo_passphrase']));
      update_option("venyo_cryptography", trim($_POST['venyo_cryptography']));
      update_option("venyo_tags", trim($_POST['venyo_tags']));
      update_option("venyo_language", trim($_POST['venyo_language']));      
      update_option("venyo_context", trim($_POST['venyo_context']));                  
      $venyo_action_result = 'Options successfully updated';
    }
  	
  	echo '<div class="wrap">';
  	echo '  <h2>Configure the Venyo Reputation Plugin</h2></br>';
  	echo '  <b>If you don\'t have a Venyo partner account, request it on <a href="https://partners.venyo.org" target="_blank">https://partners.venyo.org</a></b></br></br></br>';  	
  	echo '  <form method="post" name="venyo_options" target="_self">';
    echo '    <table width="100%" border="0" cellspacing="5" cellpadding="5">';
    echo '      <tr>';
    echo '        <td width="250">';
    echo '          Partner Name';
    echo '        </td>';
    echo '        <td>';
    echo '          <input size="30" name="venyo_partner_name" type="text" value="' . get_option('venyo_partner_name') . '" />';
    echo '        </td>';
    echo '      </tr>';
    echo '      <tr>';
    echo '        <td width="250">';
    echo '          Passphrase';
    echo '        </td>';
    echo '        <td>';
    echo '          <input size="30" name="venyo_passphrase" type="text" value="' . get_option('venyo_passphrase') . '" />';
    echo '        </td>';
    echo '      </tr>';
    echo '      <tr>';
    echo '        <td width="250">';
    echo '          Cryptography';
    echo '        </td>';
    echo '        <td>';
    echo '          <select name="venyo_cryptography">';
    echo '            <option value="md5"' . venyo_is_option_selected(get_option(venyo_cryptography),'md5') . '>' . 'md5</option>';
    echo '          </select>';
    echo '        </td>';
    echo '      </tr>';
    echo '      <tr>';
    echo '        <td width="250">';
    echo '          Default Tags (comma separated)';
    echo '        </td>';
    echo '        <td>';
    echo '          <input size="30" name="venyo_tags" type="text" value="' . get_option('venyo_tags') . '" />';
    echo '        </td>';
    echo '      </tr>';
    echo '      <tr>';
    echo '        <td width="250">';
    echo '          Default language';
    echo '        </td>';
    echo '        <td>';
    echo '          <select name="venyo_language">';
    echo '            <option value="nl"' . venyo_is_option_selected(get_option(venyo_language),'nl') . '>Dutch</option>';
    echo '            <option value="en"' . venyo_is_option_selected(get_option(venyo_language),'en') . '>English</option>';
    echo '            <option value="es"' . venyo_is_option_selected(get_option(venyo_language),'es') . '>Espanol</option>';
    echo '            <option value="fr"' . venyo_is_option_selected(get_option(venyo_language),'fr') . '>Francais</option>';
    echo '          </select>';
    echo '        </td>';
    echo '      </tr>';            
    echo '      <tr>';
    echo '        <td width="250">';
    echo '          Default Context';
    echo '        </td>';
    echo '        <td>';
    echo '          <input size="30" name="venyo_context" type="text" value="' . get_option('venyo_context') . '" />';
    echo '        </td>';
    echo '      </tr>';                
    echo '    </table>';			
		echo '    <p class="submit"><input name="submitted" type="hidden" value="yes" /><input type="submit" name="Submit" value="Update Options" /></p>';
		echo '  </form>';
		echo '</div>';    
  	echo $venyo_action_result;
  }
  
  //////////////////////////////////////////////////////////////////////////////////////////
  // This function will display the rateme button if the user is linked with a venyo user //
	//////////////////////////////////////////////////////////////////////////////////////////  
  add_filter('the_content', 'venyo_filter');
  function venyo_filter($content) 
  {
  	if ( get_usermeta(get_the_author_ID(),'venyo_user') != '' ) 
  	{
      $venyo_html = '<table width=100%><tr><td align="left">Posted by ';
      $venyo_html = $venyo_html . '<a href="http://' . get_usermeta(get_the_author_ID(),'venyo_user') . '.venyo.org" target="_blank">';
      $venyo_html = $venyo_html . get_the_author_firstname() . ' ' . get_the_author_lastname() . '</a></td><td align="right">';
      $venyo_html = $venyo_html . venyo_qi_rate_me_get_html(get_usermeta(get_the_author_ID(),'venyo_user'),get_option('venyo_partner_name'),get_option('venyo_passphrase'),get_option('venyo_cryptography'),$_SERVER['SERVER_NAME'],get_the_ID(),get_option('venyo_tags'),get_option('venyo_language'),get_permalink(get_the_ID()),get_option('venyo_context'));
	  	$venyo_html = $venyo_html . '</td></tr></table>';    
      $content = $content . $venyo_html;
    }
    return $content;
  }  
    
  /////////////////////////////////////////////////////////////////////////////////////
  // This function will display the link me and unlink me button in the user profile //
  /////////////////////////////////////////////////////////////////////////////////////
  add_action('show_user_profile', 'venyo_user_profile');
  function venyo_user_profile($userID)
  {
  	global $current_user;
  	If ( isset($_GET['signature'])  )
  	{
  	  If ( $_GET['uid'] == $current_user->ID )
  	  {
  	    If ( $_GET['signature'] == venyo_qi_link_me_get_signature_in($_GET['user'],$_GET['uid'],get_option('venyo_passphrase'),get_option('venyo_cryptography')) )
  	    {
  	    	update_usermeta($_GET['uid'],'venyo_user',$_GET['user']);
  	    }
  		}
  	}
  	
  	echo '<h3>Venyo Reputation</h3>';
  	echo '<table class="form-table">';
  	echo '<tr>';
	  If ( get_usermeta($current_user->ID,'venyo_user') != '' )
	  {
	    echo '<th>';
	    echo '<label for="vuser">';
	    echo '&nbsp;';
	    echo '</label>';
	    echo '</th>';
	    echo '<td>';
	    echo '  <table id="table-login" style="border:1px solid #BABBBC;width:340px;">';
			echo '    <tr>';
			echo '      <td style="padding-left:10px;">';
	  	echo '        Your profile is linked to Venyo user [' . get_usermeta($current_user->ID,'venyo_user') . ']';
	  	echo '      </td>';
	  	echo '    </tr>';
			echo '    <tr>';
			echo '      <td style="padding-left:10px;">';	  	
	  	echo '        <input style="vertical-align: middle;" type="checkbox" name="venyo_unlinkme" value="venyo_unlinkme" />&nbsp;Unlink Me';
	  	echo '      </td>';
	  	echo '    </tr>';
	  	echo '  </table>';	  	
	  	echo '</td>';
	  }
	  else
	  {
 		  echo '<th>';
 		  echo '  <label for="vuser">';
 		  echo '    <a href="https://www.venyo.org/signup/?partner=' . get_option('venyo_partner_name') . '" target="_blank">Not yet a Venyo user?</a>';
 		  echo '  </label>';
 		  echo '</th>';
	    echo '<td>';
			echo '  <table id="table-login" style="border:1px solid #BABBBC;width:340px;">';
			switch (strtoupper($_GET['error']))
			{
				case "A000001":
				  $venyo_error_message = 'Signature field received from partner is not correct';
				  break;
				case "A000002":
				  $venyo_error_message = 'Missing username field';
				  break;
				case "A000003":
				  $venyo_error_message = 'Missing password field';
				  break;
				case "A000004":
				  $venyo_error_message = 'Missing partner name, uid or signature field';
				  break;
				case "A000005":
				  $venyo_error_message = 'Invalid username or password field';
				  break;
				case "A000006":
				  $venyo_error_message = 'Missing username or password field';
				  break;
				case "A000007":
				  $venyo_error_message = 'Invalid or blocked partner';
				  break;				  				  				  				  				  				  
			}
			if ($venyo_error_message != '')
			{
				echo '      <tr><td style="color: red;font-weight: bold;padding-left:10px;">Error : ' . $venyo_error_message . '</td></tr>';
			}
			echo '    <tr>';
			echo '      <td style="padding-left:10px;">';
			echo '        Venyo username<br/><input id="venyo_user" name="venyo_user" size="50" type="text" maxlength="64" value="" />';
			echo '      </td>';
			echo '    </tr>';
			echo '    <tr>';
			echo '      <td style="padding-left:10px;">';
			echo '        Venyo password<br/><input id="venyo_password" name="venyo_password" size="40" type="password" maxlength="64" value="" />';
			echo '      </td>';
			echo '    </tr>';
			echo '    <tr>';
			echo '      <td style="text-align: center;">';
			echo '        <img alt="Powered by venyo.org" title="Powered by venyo.org" src="http://get.venyo.org/poweredbyvenyo" />';
			echo '      </td>';
			echo '    </tr>';
			echo '  </table>';
			echo '</td>';
	  }
		echo '</tr>';
		echo '</table>';
  }
  
  /////////////////////////////////////////////////////////////////////////
  // This function will link or unlink profile when updating the profile //
  /////////////////////////////////////////////////////////////////////////
  add_action('profile_update','venyo_user_profile_update');
  function venyo_user_profile_update($userID)  
  {
  	global $current_user;
  	If ( ( $_POST['venyo_user'] != '' ) AND ( $_POST['venyo_password'] != '' ) )
  	{
  		$newurl = 'https://www.venyo.org/linkme/';
  		$newurl = $newurl . '?user=' . $_POST['venyo_user'];
  		$newurl = $newurl . '&password=' . $_POST['venyo_password'];
  		$newurl = $newurl . '&back_url_success=' . urlencode(cur_page_url());
  		$newurl = $newurl . '&back_url_failure=' . urlencode(cur_page_url());
  		$newurl = $newurl . '&signature=' . venyo_qi_link_me_get_signature_out(get_option('venyo_partner_name'),$current_user->ID,get_option('Venyo_PassPhrase'),get_option('Venyo_Cryptography'));
  		$newurl = $newurl . '&partner=' . get_option('venyo_partner_name');
  		$newurl = $newurl . '&uid=' . $current_user->ID;
  		header('Location: ' . $newurl );
    	exit;
    }
    If ( $_POST['venyo_unlinkme'] != '' )
    {
     	update_usermeta($current_user->ID,'venyo_user','');    	
    }
  }
    
  function venyo_qi_rate_me_get_html($VenyoUserName,$VenyoPartnerName,$VenyoPassphrase,$VenyoCryptography,$VenyoReferer,$VenyoUid,$VenyoTags,$VenyoLanguage,$VenyoPermalink,$VenyoContext)
  {
  	$VenyoReferer = venyo_qi_referer_clean($VenyoReferer);
  	
  	$VenyoPartnerName = strtolower($VenyoPartnerName);
  	$VenyoUserName = strtolower($VenyoUserName);
  	$VenyoReferer = strtolower($VenyoReferer);
  	$VenyoUid = strtolower($VenyoUid);
  	
  	$VenyoSignature = md5('partner='.$VenyoPartnerName.'&user='.$VenyoUserName.'&uid='.$VenyoUid.'&referer='.$VenyoReferer.'&passphrase='.$VenyoPassphrase);
  	
  	$VenyoLink = '<a target=\'venyo\' href=\'https://evaluation.venyo.org/?partner='.urlencode($VenyoPartnerName).
  			'&user='.urlencode($VenyoUserName).'&uid='.urlencode($VenyoUid).'&signature='.$VenyoSignature.'&tags='.urlencode($VenyoTags);
  			
  	if ($VenyoLanguage != '')
  	{
  		$VenyoLink .= "&language=".$VenyoLanguage;
  	}
  	if ($VenyoPermalink != '')
  	{
  		$VenyoLink .= "&permalink=".urlencode($VenyoPermalink);
  	}
  	if ($VenyoContext != '')
  	{
  		$VenyoLink .= "&context=".$VenyoContext;
  	}
  	
  	$VenyoLink .= '\'>';
  	
  	$VenyoSignature = md5('partner='.$VenyoPartnerName.'&user='.$VenyoUserName.'&referer='.$VenyoReferer.'&passphrase='.$VenyoPassphrase);
  	
  	$VenyoLink .= '<img style=\'border:0px;\' alt=\'venyo.org - '.$VenyoUserName.'\' title=\'venyo.org - '.$VenyoUserName.'\'
  			src=\'http://get.venyo.org/rateme/?partner='.urlencode($VenyoPartnerName).'&user='.urlencode($VenyoUserName).'&signature='.$VenyoSignature;
  	
  	if ($VenyoLanguage != '')
  	{
  		$VenyoLink .= "&language=".$VenyoLanguage;
  	}
  	$VenyoLink .= '\' /></a>';
  
  	return($VenyoLink);	
  }	
  
  function venyo_qi_referer_clean($VenyoReferer)
  {
  	$VenyoPosition = strpos($VenyoReferer,"://");
  	
  	if ($VenyoPosition > 0)
  	{
  		$VenyoReferer = substr($VenyoReferer,$VenyoPosition + 3);
  	}
  	
  	$VenyoPosition = strpos($VenyoReferer,"/");
  	
  	if ($VenyoPosition > 0)
  	{
  		$VenyoReferer = substr($VenyoReferer,0,$VenyoPosition);
  	}
  	
  	$VenyoPosition = strpos($VenyoReferer,"?");
  	
  	if ($VenyoPosition > 0)
  	{
  		$VenyoReferer = substr($VenyoReferer,0,$VenyoPosition);
  	}
  	
  	$VenyoPosition = strpos($VenyoReferer,"#");
  	
  	if ($VenyoPosition > 0)
  	{
  		$VenyoReferer = substr($VenyoReferer,0,$VenyoPosition);
  	}
  	return($VenyoReferer);
  }
  
  function venyo_qi_link_me_get_signature_out($VenyoPartnerName,$VenyoUid,$VenyoPassphrase,$VenyoCryptography)
	{
		$VenyoPartnerName = strtolower($VenyoPartnerName);
		$VenyoUid = strtolower($VenyoUid);
		return(md5('partner='.$VenyoPartnerName.'&uid='.$VenyoUid.'&passphrase='.$VenyoPassphrase));
	}
	
	function venyo_qi_link_me_get_signature_in($VenyoUserName,$VenyoUid,$VenyoPassphrase,$VenyoCryptography)
	{
		$VenyoUserName = strtolower($VenyoUserName);
		$VenyoUid = strtolower($VenyoUid);
		return(md5('user='.$VenyoUserName.'&uid='.$VenyoUid.'&passphrase='.$VenyoPassphrase));
	}
	
	function venyo_qi_unlink_me_get_signature_out($VenyoPartnerName,$VenyoUid,$VenyoPassphrase,$VenyoCryptography)
	{
		$VenyoPartnerName = strtolower($VenyoPartnerName);
		$VenyoUid = strtolower($VenyoUid);
		return(md5('partner='.$VenyoPartnerName.'&uid='.$VenyoUid.'&passphrase='.$VenyoPassphrase));
	}
	
	function venyo_qi_unlink_me_get_signature_in($VenyoUid,$VenyoPassphrase,$VenyoCryptography)
	{
		$VenyoUid = strtolower($VenyoUid);
		return(md5('uid='.$VenyoUid.'&passphrase='.$VenyoPassphrase));
	}
	
	function cur_page_url() 
	{
 		$pageURL = 'http';
 		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 		$pageURL .= "://";
 		if ($_SERVER["SERVER_PORT"] != "80") 
 		{
  		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 		}
 		else 
 		{
  		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 		}
 		return $pageURL;
	}
	
	function venyo_is_option_selected($a,$b)
	{
		if ($a == $b)
		{
		  return ' selected="selected" ';
		}
		else
		{
			return '';
		}
	}
	
?>