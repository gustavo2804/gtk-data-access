<?php

class RequestPasswordResetController
{
    public function getPersonaDataAccess()
    {
        return DataAccessManager::get("persona");
    }

    public function handleUserRequestPasswordResetLinkForUserID($userIdentifier, $delegate = null)
    {
        $user   = $this->getPersonaDataAccess()->findUserByCedula($userIdentifier);
		$userID = null;

        if (!$user)
        {
            $user = $this->getPersonaDataAccess()->getOne("email", $userIdentifier);
			$userID = $this->getPersonaDataAccess()->valueForKey("id", $user);
        }

		if (!$user)
		{
			$user = $this->getPersonaDataAccess()->getOne("id", $userIdentifier);
			$userID = $userIdentifier;
		}
 
        if (!$user)
        {
            return new FormResult(0, Glang::get("RequestPasswordReset/Form/user_not_found"));
        }

		$hasRequestedLinkInTheLast5Minutes = DataAccessManager::get("SetPasswordTokenDataAccess")->hasUserRequestedALinkInTheLast5Minutes($user);

		if ($hasRequestedLinkInTheLast5Minutes)
		{
			die(Glang::get("RequestPasswordReset/Form/too_soon_for_new_password_token"));
		}

        try
        {
            $result = $this->sendResetPasswordLinkFromAdminForUser($user, [
				"origin" => "USER_REQUEST",
			]);

            return new FormResult(1, Glang::get("RequestPasswordReset/Form/submitted"));
		
		}
        catch (Exception $e)
        {
            gtk_log("handleUserRequestPasswordResetLinkForUserID Excepetion".$e->getMessage());
            return new FormResult(0, "RequestPasswordReset/Form/error_sending_reset_password_token");
        }
    }


	public function passwordSetLinkForUser($persona, $origin = null)
	{
		$debug = false;

		$token = DataAccessManager::get('SetPasswordTokenDataAccess')->createTokenForUserFromOrigin($persona, $origin);


        $host = $_SERVER["SERVER_NAME"];
    
        $linkToToken = "https://".$host."/auth/passwordSetLink.php?token=".$token;
		
        if ($debug)
        {
            error_log("`passwordSetLinkForUser` --- Sending reset password link for persona: ".serialize($persona));
            error_log("`passwordSetLinkForUser` --- Sending reset password link for token: ".serialize($token));
			error_log("Link: ".$linkToToken);
        }

		return $linkToToken;
	}


    public function sendResetPasswordLinkFromAdminFromUserToUserDelegateOptions($user, &$item, &$delegate, $options = null)
	{
        die($this->sendResetPasswordLinkFromAdminForUser($item));
    }

	public function sendResetPasswordLinkFromAdminForUser($user, $options = null)
	{
        $debug = false;

		$origin = null;

		if (isset($options["origin"]))
		{
			$origin = $options["origin"];
		}
		else
		{
			$origin = "ADMIN_SET";
		}


        $persona = $user;

        $email = $this->getPersonaDataAccess()->valueForKey("email", $persona);

        if (!$email)
        {
            if ($debug)
            {
                error_log("No emeail found on user. Will throw exception");
            }
			$message = Glang::get("RequestPasswordReset/Form/no_email_for_user");

            throw new Exception($message);
            return new FormResult(0, $message);
        }

        if ($debug)
        {
            error_log("`sendResetPasswordLinkFromAdminForUser` - Sending reset password link for persona: ".serialize($persona));
        }
		
		$linkToToken = $this->passwordSetLinkForUser($persona, $origin);

		$text = Glang::get("reset_password_email/body", [
			"linkToToken" => $linkToToken,
		]);

		if ($debug)
		{
			error_log("Got text for email: ".$text);
		}

        DataAccessManager::get("EmailQueueManager")->addToQueue(
            $email,
            Glang::get("reset_password_email/subject"),
            $text, [
				"isHTML" => true,
			]);

        if ($debug)
        {
            error_log("Did queue password link for persona");
        }

        if ($origin == "ADMIN_SET")
        {
            return Glang::get("RequestPasswordReset/Form/submitted");
        }
        else
        {
            return Glang::get("RequestPasswordReset/Form/submitted");
        }
    }
}
