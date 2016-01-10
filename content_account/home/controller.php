<?php
namespace content_account\home;

class controller extends \mvc\controller
{
	function _route()
	{
		$mymodule = $this->module();

		// check access permission to account
		$myphrase = \lib\utility::get('cp');
		if($myphrase)
			\lib\utility\Cookie::write('cp', $myphrase, 60*60*24*7); // allow 1week
		elseif(! \lib\utility\Cookie::read('cp') && $mymodule !=='logout')
			\lib\error::login();


		$referer  = \lib\router::urlParser('referer', 'domain');
		$from     = \lib\utility\Cookie::read('from');
		$from     = $from ? $from : \lib\utility::get('from');
		$islogin  = $this->login();
		// set referrer in cookie
		if($referer !== Domain)
			\lib\utility\Cookie::write('referer', $referer, 60*15);
		// check permission for changepass
		if($mymodule === 'changepass' && $from !== 'verification' && !$islogin)
			\lib\error::access(T_("you can't access to this page!"));

		switch ($mymodule)
		{
			case 'home':
				$this->redirector()->set_url("login")->redirect();
				break;


			case 'verification':
			case 'verificationsms':
				if($from !== 'recovery' && $from !== 'signup' && $from !== 'verification')
					\lib\error::access(T_("you can't access to this page!"));
				$this->model_name   = 'content_account\\'.$mymodule.'\model';
				$this->display_name = 'content_account\\'.$mymodule.'\display.html';
				$this->post($mymodule)->ALL();
				$this->get()          ->ALL();
				break;

			case 'signup':
				return;
				/** 

				 Fix it later, only access if posible
				 */

			case 'login':
			case 'recovery':
				if($islogin )
				{
					\lib\debug::true(T_("you are logined to system!"));
					$myreferer = \lib\router::urlParser('referer', 'host');
					$myssid    = isset($_SESSION['ssid'])? '?ssid='.$_SESSION['ssid']: null;

					if(\lib\router::get_storage('CMS'))
					{
						$this->redirector()->set_domain()->set_sub_domain(\lib\router::get_storage('CMS') )->set_url()->redirect();
					}
					else
						$this->redirector()->set_domain()->set_url()->redirect();
				}
			case 'changepass':
				$this->model_name   = 'content_account\\'.$mymodule.'\model';
				$this->display_name = 'content_account\\'.$mymodule.'\display.html';
				$this->post($mymodule)->ALL();
				$this->get()          ->ALL();
				break;


			case 'smsdelivery':
			case 'smscallback':
				$uid = 201500001;
				if(\lib\utility::get('uid') == $uid || \lib\utility\Cookie::read('uid') == $uid)
				{
					$this->model_name	= 'content_account\sms\model';
					$this->display_name	= 'content_account\sms\display.html';
					$this->post($mymodule)->ALL();
					$this->get($mymodule) ->ALL();
				}
				else
					\lib\error::access("SMS");
				break;


			// logout user from system then redirect to ermile
			case 'logout':
				$this->model_name	= 'mvc\model';
				$this->model()->put_logout();
				$this->redirector()->set_domain()->set_url()->redirect();
				break;


			default:
				\lib\error::page();
				break;
		}
		$this->route_check_true = true;
	}
}
?>