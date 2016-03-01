<?php
namespace lib\mvc;
use \lib\router;
class controller extends \lib\controller
{
	use \lib\mvc\controllers\access;
	use \lib\mvc\controllers\login;
	use \lib\mvc\controllers\sessions;
	use \lib\mvc\controllers\template;
	use \lib\mvc\controllers\tools;
	use \lib\mvc\controllers\url;


	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		parent::__construct();
		if(MyAccount && SubDomain == null)
		{
			if(is_string(constant('Account')) && constant('Account') === constant('MainService'))
				$domain = MainService.MainTld;
			else
				$domain = null;
			$param = $this->url('param');
			if($param)
				$param = '?'.$param;

			switch ($this->module())
			{
				case 'signin':
				case 'login':
					$this->redirector()->set_domain($domain)->set_url(MyAccount. '/login'.$param)->redirect();
					break;

				case 'signup':
				case 'register':
					$this->redirector()->set_domain($domain)->set_url(MyAccount. '/signup'.$param)->redirect();
					break;

				case 'signout':
				case 'logout':
					// if(Domain !== MainService)
						// $this->redirector()->set_domain(MainService.'.'.Tld)->set_url('logout')->redirect();
					$this->redirector()->set_domain()->set_url(MyAccount. '/logout'.$param)->redirect();
					break;
			}
		}
		$myrep = router::get_repository_name();

		// running template base module for homepage
		if(\lib\router::get_storage('CMS') && $myrep === 'content' && method_exists($this, 's_template_finder') && get_class($this) === 'content\home\controller')
		{
			$this->s_template_finder();
		}
	}
}
?>