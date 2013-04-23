<?php namespace Think\Auths;

interface AuthInterface
{
	public function check();

	public function login();
}