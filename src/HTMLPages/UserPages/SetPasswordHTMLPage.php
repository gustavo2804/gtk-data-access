<?php


class SetPasswordHTMLPage extends GTKHTMLPage
{
    public $token;
    // public $messages;

    public function processGet($getObject)
    {
        if (!isset($_GET['token']) || empty($_GET['token'])) 
        {
            return $this->redirectToResetPasswordWithMessage("ResetPassword/Form/InvalidRequest");
        }
        
        $this->token = $_GET['token'];
        
        if (!DataAccessManager::get("SetPasswordTokenDataAccess")->isValidToken($this->token)) 
        {
            return $this->redirectToResetPasswordWithMessage("ResetPassword/Form/InvalidToken");
        }
    }

    public function redirectToResetPasswordWithMessage($message)
    {
        echo Glang::get($message);
        header("Refresh:3; url=/auth/requestPasswordToken.php");
        exit();
    }

    public function processPost()
    {
        $debug = false;

        $this->token = $_POST['token'];
        $newPassword = $_POST['newPassword'];
    
        $tokenDataAccess = DataAccessManager::get("SetPasswordTokenDataAccess");
    
        if (!$tokenDataAccess->isValidToken($this->token))
        {
            return $this->redirectToResetPasswordWithMessage("ResetPassword/Form/InvalidToken");
        }  

        if ($debug)
        {
            error_log("Token is valid. Initiating MethodLogger.");
        }

        $tokenKeeper = new MethodLogger();

        if (!validatePasswordIsSecure($newPassword, $tokenKeeper))
        {
            if ($debug)
            {
                error_log("Password is NOT secure. Preparing methods.");
            }
            $this->messages = $tokenKeeper->methodsLogged();
            return;
        }

        if ($tokenDataAccess->resetPasswordForToken($this->token, $newPassword))
        {

            echo Glang::get("ResetPassword/Form/Success");
            header("Refresh:3; url=/auth/login.php");
            exit();
        } 
        else 
        {
            echo "Error resetting password.";
            die();
        }
    }

	
	public function renderMessages($get, $post, $server, $cookie, $session, $files, $env)
	{
		$toReturn = "";

		if (count($this->messages) > 0)
		{
			$toReturn .= "<h1 class='font-bold'>";
			$toReturn .= "Mensajes";
			$toReturn .= "</h1>";
			$toReturn .= "<div>";
			foreach ($this->messages as $message)
			{
				$toReturn .= "<div>";
				if (is_string($message))
				{
                    global $_GLOBALS;
					$toReturn .= Glang::get($message, $_GLOBALS["APP_PASSWORD_REQUIREMENTS"]);
				}
				else
				{
					$toReturn .= print_r($message, true);
				}
				$toReturn .= "</div>";
			}
			$toReturn .= "</div>";
		}

		return $toReturn;
	}

    public function renderBody()
    {
        ob_start(); ?>
    
        <h1>Reset Your Password</h1>

        <?php
        echo $this->renderMessages($get, $post, $server, $cookie, $session, $files, $env);
        ?>

        <form action="/auth/passwordSetLink.php" method="post">
            <input type="hidden" 
                   name="token"
                   class="block text-sm font-medium text-gray-700" 
                   value="<?php echo htmlspecialchars($this->token); ?>">
    
            <label for="newPassword">
                New Password:
            </label>
            <br>
            <input type="password" 
                   id="newPassword" 
                   name="newPassword" 
                   required
                   class="mt-1 block w-full border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <br>
            <br>
    
            <input type="submit" 
                   value="Reset Password"
                   class="px-8 w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded">
        </form>
    
        <?php return ob_get_clean();
    }
    
}
