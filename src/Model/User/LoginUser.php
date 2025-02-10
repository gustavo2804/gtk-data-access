<?php


class LoginUser
{
	public $oDelegate;

	public function setDelegate($delegate)
	{
		$this->oDelegate = $delegate;
	}

	public function verifyPassword($password, $passwordHash)
	{
		return password_verify($password, $passwordHash);
	}

	public function loginOnApache()
	{
		$debug = false;


		if ($_SERVER["REQUEST_METHOD"] === "POST")
		{
			$userName = $_POST["user_id"];
			$password = $_POST["password"];

			if ($debug)
			{
				error_log("User ID: ".$userName);
				error_log("Password: ".$password);
				error_log("_POST_".print_r($_POST, true));
			}

			$this->loginUser($userName, $password);
		}
		else
		{
			// $this->loginUser($_GET["user_id"], $_GET["password"]);
		}
	}

	public function loginUser($userName, $password, $pDelegate = null)
	{
		$debug    = true;
		$delegate = null;

		if (!$pDelegate)
		{
			if (!$this->oDelegate)
			{
				throw new Exception("Needs delegate to process login");
			}

			$delegate = $this->oDelegate;
		}
		else
		{
			$delegate = $pDelegate;
		}

		if ($debug)
		{
			error_log("delegate is ".get_class($delegate));
		}

		if (!$userName || !$password)
		{
			$missingFields = [];

			if (!$userName)
			{
				$missingFields[] = "userName";  
			}

			if (!$password)
			{
				$missingFields[] = "password";
			}

			return $delegate->incompleteData($missingFields);
		}
		$user_data_access = DataAccessManager::get("persona");

		$keysToTry = [
			"cedula",
			"email",
		];

		$user = null;

		foreach ($keysToTry as $key)
		{
			if (!$user)
			{
				$user = $user_data_access->getOne($key, $userName);
				if($debug){
					error_log("el usuario ingresado fue: ".print_r($user,true));
				}
			}
			else
			{
				break;
			}
		}

		if (!$user)
		{
			return $delegate->userDoesNotExist();
		}

		if (!$user_data_access->isActive($user))
		{
			if($debug)
			{
				gtk_log("`LoginPage` - no esta activo");
			}
			return $delegate->userIsNotActive($user);
		}

		$passwordHash = $user_data_access->valueForKey("password_hash", $user);

		if ($debug)
		{
			error_log("Got password_hash: ".$passwordHash);
		}

		if (!isTruthy($passwordHash))
		{
			return $delegate->userDoesNotHavePassword($user);
		}

		$passwordIsCorrect = $this->verifyPassword($password, $passwordHash);

		if ($debug)
		{
			error_log("Password is correct!!");
		}

		if (!$passwordIsCorrect)
		{
			$maxLoginAttempts = 9;
			$loginAttempts    = 0;

			$tooManyLoginAttempts = ($loginAttempts >= $maxLoginAttempts);

			if ($tooManyLoginAttempts)
			{
				return $delegate->tooManyLoginAttempts();
			}
			else
			{
				return $delegate->userAndPasswordDoNotMatch($user);
			}
			
		}

		return $delegate->successfulMatchForUser($user);
	}
}



