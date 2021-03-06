<?php
namespace addons\content_account\signup;
use \lib\utility;
use \lib\debug;

class model extends \mvc\model
{
	/**
	 * signup to system
	 * @return [type] [description]
	 */
	public function post_signup()
	{
		// get parameters and set to local variables
		$mymobile   = utility::post('mobile', 'filter');
		$mypass     = utility::post('password', 'hash');
		$myperm     = $this->option('account');
		if(!$myperm)
		{
			$myperm = 'NULL';
		}
		$user_id     = \lib\db\users::signup($mymobile, $mypass, $myperm);

		if($user_id)
		{
			// generate verification code
			// save in logs table
			// set SESSION verification_mobile
			$code = \lib\utility\filter::generate_verification_code($user_id, $mymobile);
			if($code)
			{
				\lib\utility\sms::send($mymobile, 'signup', $code);
				debug::true(T_("Register successfully"));

				$this->redirector()->set_url('verification?from=signup&mobile='.$mymobile);
				// $this->redirector()->set_url('login?from=signup&cp=1&mobile='.$mymobile);
			}
			else
			{
				debug::error(T_("Please contact to administrator!"));
			}
		}
		elseif($user_id === false)
		{
			debug::error(T_("Mobile number exist!"));
		}
		else
		{
			debug::error(T_("Please contact to administrator!"));
		}
	}
}
?>