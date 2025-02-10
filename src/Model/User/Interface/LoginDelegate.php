<?php

interface LoginDelegate
{
	public function userDoesNotExist();
	public function userDoesNotHavePassword($user);
	public function userIsNotActive($user);
	public function userAndPasswordDoNotMatch();
	public function tooManyLoginAttempts();
	public function successfulMatchFromUntrustedDevice();
	public function successfulMatch();
}