<?php

define('INSIDE', true);

$_EnginePath = './';

include($_EnginePath.'common.php');

	includeLang('reg_ajax');
	$Now = time();
	
	if(REGISTER_RECAPTCHA_ENABLE)
	{
		require($_EnginePath.'includes/recaptchalib.php');
	}
	
	header('access-control-allow-origin: *');
	
	if(isset($_GET['register']))
	{
		$JSONResponse = null;
		$JSONResponse['Errors'] = array();
		
		// User is trying to register
		$Username = (isset($_GET['username']) ? trim($_GET['username']) : null);
		$Password = (isset($_GET['password']) ? trim($_GET['password']) : null);
		$Email = (isset($_GET['email']) ? trim($_GET['email']) : null);
		$CheckEmail = $Email;
		$Email = mysql_real_escape_string($Email);
		$Rules = (isset($_GET['rules']) ? $_GET['rules'] : null);
		$GalaxyNo = (isset($_GET['galaxy']) ? intval($_GET['galaxy']) : null);

		// Check if Username is correct
		$UsernameGood = false;
		if(strlen($Username) < 4)
		{
			// Username is too short
			$JSONResponse['Errors'][] = 1;
			$JSONResponse['BadFields'][] = 'username';
		}
		else if(strlen($Username) > 64)
		{
			// Username is too long
			$JSONResponse['Errors'][] = 2;
			$JSONResponse['BadFields'][] = 'username';
		}
		else if(!preg_match(REGEXP_USERNAME_ABSOLUTE, $Username))
		{
			// Username has illegal signs
			$JSONResponse['Errors'][] = 3;
			$JSONResponse['BadFields'][] = 'username';
		}
		else
		{
			$UsernameGood = true;
		}
		
		// Check if Password is correct
		if(strlen($Password) < 4)
		{
			// Password is too short
			$JSONResponse['Errors'][] = 4;
			$JSONResponse['BadFields'][] = 'password';
		}
		
		// Check if EMail is correct
		$EmailGood = false;
		$BannedDomains = str_replace('.', '\.', $_GameConfig['BannedMailDomains']);
		if(empty($Email))
		{
			// EMail is empty
			$JSONResponse['Errors'][] = 5;
			$JSONResponse['BadFields'][] = 'email';
		}
		else if($Email != $CheckEmail)
		{
			// EMail has illegal signs
			$JSONResponse['Errors'][] = 6;
			$JSONResponse['BadFields'][] = 'email';
		}
		else if(!is_email($Email))
		{
			// EMail is incorrect
			$JSONResponse['Errors'][] = 7;
			$JSONResponse['BadFields'][] = 'email';
		}
		else if(!empty($BannedDomains) && preg_match('#('.$BannedDomains.')+#si', $Email))
		{
			// EMail is on banned domains list
			$JSONResponse['Errors'][] = 8;
			$JSONResponse['BadFields'][] = 'email';
		}
		else
		{
			$EmailGood = true;
		}
		
		// PreCheck Galaxy
		if($GalaxyNo < 1)
		{
			// Galaxy not given
			$JSONResponse['Errors'][] = 13;
			$JSONResponse['BadFields'][] = 'galaxy';
		}
		else if($GalaxyNo > MAX_GALAXY_IN_WORLD)
		{
			// GalaxyNo is too high
			$JSONResponse['Errors'][] = 14;
			$JSONResponse['BadFields'][] = 'galaxy';
		}
		
		// Check if Rules has been accepted
		if($Rules != 'on')
		{
			// Rules are not accepted
			$JSONResponse['Errors'][] = 9;
		}
		
		if(REGISTER_RECAPTCHA_ENABLE)
		{
			// Check if reCaptcha is correct
			if(!isset($_GET['recaptcha_challenge_field']))
			{
				$_GET['recaptcha_challenge_field'] = null;
			}
			if(!isset($_GET['recaptcha_response_field']))
			{
				$_GET['recaptcha_response_field'] = null;
			}
			$resp = recaptcha_check_answer(REGISTER_RECAPTCHA_PRIVATEKEY, $_SERVER['REMOTE_ADDR'], $_GET['recaptcha_challenge_field'], $_GET['recaptcha_response_field']);
			if(!$resp->is_valid)
			{
				// ReCaptcha Code is not valid
				$JSONResponse['Errors'][] = 10;
			}
		}

		if($EmailGood === true AND $UsernameGood === true)
		{
			$Query_CheckExistance = '';
			$Query_CheckExistance .= "SELECT `username`, `email` FROM {{table}} ";
			$Query_CheckExistance .= "WHERE `username` = '{$Username}' OR `email` = '{$Email}' LIMIT 2;";
			$Result_CheckExistance = doquery($Query_CheckExistance, 'users');
			if(mysql_num_rows($Result_CheckExistance) > 0)
			{
				while($FetchData = mysql_fetch_assoc($Result_CheckExistance))
				{
					if(strtolower($FetchData['username']) == strtolower($Username))
					{
						// Username is used
						$JSONResponse['Errors'][] = 11;
						$JSONResponse['BadFields'][] = 'username';
					}
					else
					{
						// EMail is used
						$JSONResponse['Errors'][] = 12;
						$JSONResponse['BadFields'][] = 'email';
					}
				}
			}
		}

		if(empty($JSONResponse['Errors']))
		{
			unset($JSONResponse['Errors']);
			
			// Check Galaxy
			$SystemsRange = 25;
			$SystemRandom = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
			if(($SystemRandom + $SystemsRange) >= MAX_SYSTEM_IN_GALAXY)
			{
				$System_Lower = $SystemRandom - $SystemsRange;
			}
			else
			{
				$System_Lower = $SystemRandom;
			}
			$System_Higher = $System_Lower + $SystemsRange;
			$Planet_Lower = 4;
			$Planet_Higher = 12;
			
			// - Step 1: check random range of solar systems
			$PosFound = false;
			$Position_NonFree = array();
			$Position_NonFreeCount = 0;
			$Position_TotalCount = (($System_Higher - $System_Lower) + 1) * (($Planet_Higher - $Planet_Lower) + 1);

			$Query_CheckGalaxy1 = '';
			$Query_CheckGalaxy1 .= "SELECT `system`, `planet` FROM {{table}} ";
			$Query_CheckGalaxy1 .= "WHERE `galaxy` = {$GalaxyNo} AND ";
			$Query_CheckGalaxy1 .= "`system` BETWEEN {$System_Lower} AND {$System_Higher} AND ";
			$Query_CheckGalaxy1 .= "`planet` BETWEEN {$Planet_Lower} AND {$Planet_Higher};";
			$Result_CheckGalaxy1 = doquery($Query_CheckGalaxy1, 'galaxy');
			if(mysql_num_rows($Result_CheckGalaxy1) > 0)
			{
				while($FetchData = mysql_fetch_assoc($Result_CheckGalaxy1))
				{
					$Position_NonFree["{$FetchData['system']}:{$FetchData['planet']}"] = true;
				}
				$Position_NonFreeCount = count($Position_NonFree);
			}
			if($Position_NonFreeCount < $Position_TotalCount)
			{
				while(!$PosFound)
				{
					$System = mt_rand($System_Lower, $System_Higher);
					$Planet = mt_rand($Planet_Lower, $Planet_Higher);
					if(!isset($Position_NonFree["{$System}:{$Planet}"]))
					{
						$PosFound = true;
					}
				}
			}
			else
			{
				// - Step 2: check whole galaxy, if space not found earlier
				$Position_NonFree = array();
				$Position_NonFreeCount = 0;
				$Position_TotalCount = MAX_SYSTEM_IN_GALAXY * (($Planet_Higher - $Planet_Lower) + 1);
				
				$Query_CheckGalaxy2 = '';
				$Query_CheckGalaxy2 .= "SELECT `system`, `planet` FROM {{table}} ";
				$Query_CheckGalaxy2 .= "WHERE `galaxy` = {$GalaxyNo} AND ";
				$Query_CheckGalaxy2 .= "`planet` BETWEEN {$Planet_Lower} AND {$Planet_Higher};";
				$Result_CheckGalaxy2 = doquery($Query_CheckGalaxy2, 'galaxy');
				if(mysql_num_rows($Result_CheckGalaxy2) > 0)
				{
					while($FetchData = mysql_fetch_assoc($Result_CheckGalaxy2))
					{
						$Position_NonFree["{$FetchData['system']}:{$FetchData['planet']}"] = true;
					}
					$Position_NonFreeCount = count($Position_NonFree);
				}
				if($Position_NonFreeCount < $Position_TotalCount)
				{
					while(!$PosFound)
					{
						$System = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
						$Planet = mt_rand($Planet_Lower, $Planet_Higher);
						if(!isset($Position_NonFree["{$System}:{$Planet}"]))
						{
							$PosFound = true;
						}
					}
				}
				else
				{
					// - Step 3: check whole galaxy and all slots which has not been checked
					$Position_NonFree = array();
					$Position_NonFreeCount = 0;
					for($i = 1; $i < $Planet_Lower; $i += 1)
					{
						$Planet_PosArray[] = $i;
					}
					for($i = $Planet_Higher; $i < MAX_PLANET_IN_SYSTEM; $i += 1)
					{
						$Planet_PosArray[] = $i;
					}
					$Position_TotalCount = MAX_SYSTEM_IN_GALAXY * count($Planet_PosArray);
					
					$Query_CheckGalaxy3 = '';
					$Query_CheckGalaxy3 .= "SELECT `system`, `planet` FROM {{table}} ";
					$Query_CheckGalaxy3 .= "WHERE `galaxy` = {$GalaxyNo} AND ";
					$Query_CheckGalaxy3 .= "`planet` NOT BETWEEN {$Planet_Lower} AND {$Planet_Higher};";
					$Result_CheckGalaxy3 = doquery($Query_CheckGalaxy3, 'galaxy');
					if(mysql_num_rows($Result_CheckGalaxy3) > 0)
					{
						while($FetchData = mysql_fetch_assoc($Result_CheckGalaxy3))
						{
							$Position_NonFree["{$FetchData['system']}:{$FetchData['planet']}"] = true;
						}
						$Position_NonFreeCount = count($Position_NonFree);
					}
					if($Position_NonFreeCount < $Position_TotalCount)
					{
						while(!$PosFound)
						{
							$System = mt_rand(1, MAX_SYSTEM_IN_GALAXY);
							$Planet = $Planet_PosArray[array_rand($Planet_PosArray)];
							if(!isset($Position_NonFree["{$System}:{$Planet}"]))
							{
								$PosFound = true;
							}
						}
					}
				}
			}
			
			if($PosFound)
			{
				$Query_InsertUser = '';
				$Query_InsertUser .= "INSERT INTO {{table}} SET ";
				$Query_InsertUser .= "`username` = '{$Username}', ";
				$Query_InsertUser .= "`email` = '{$Email}', ";
				$Query_InsertUser .= "`email_2` = '{$Email}', ";
				$Query_InsertUser .= "`ip_at_reg` = '{$_SERVER['REMOTE_ADDR']}', ";
				$Query_InsertUser .= "`id_planet` = 0, ";
				$Query_InsertUser .= "`register_time` = {$Now}, ";
				$Query_InsertUser .= "`onlinetime` = {$Now} - (24*60*60), ";
				$Query_InsertUser .= "`rules_accept_stamp` = {$Now}, ";
				$Query_InsertUser .= "`password` = MD5('{$Password}');";
				doquery($Query_InsertUser, 'users');

				// Get UserID
				$Query_GetUserID = "SELECT LAST_INSERT_ID() AS `ID`;";
				$Result_GetUserID = doquery($Query_GetUserID, '', true);
				$UserID = $Result_GetUserID['ID'];

				// Update all MailChanges
				doquery("UPDATE {{table}} SET `ConfirmType` = 4 WHERE `NewMail` = '{$Email}' AND `ConfirmType` = 0;", 'mailchange');

				// Create a Planet for User
				include($_EnginePath.'includes/functions/CreateOnePlanetRecord.php');
				$Galaxy = $GalaxyNo;
				$PlanetID = CreateOnePlanetRecord($Galaxy, $System, $Planet, $UserID, $_Lang['MotherPlanet'], true);
				
				// Update Config
				$_GameConfig['users_amount'] += 1;
				$Query_UpdateConfig = '';
				$Query_UpdateConfig .= "UPDATE {{table}} ";
				$Query_UpdateConfig .= "SET `config_value` = {$_GameConfig['users_amount']} ";
				$Query_UpdateConfig .= "WHERE `config_name` = 'users_amount';";
				doquery($Query_UpdateConfig, 'config');
				$_MemCache->GameConfig = $_GameConfig;

				// Update User with new data
				if(isset($_COOKIE[REFERING_COOKIENAME]) && $_COOKIE[REFERING_COOKIENAME] > 0)
				{
					$RefID = round($_COOKIE[REFERING_COOKIENAME]);
					if($RefID > 0)
					{
						$Query_SelectReferrer = "SELECT `id` FROM {{table}} WHERE `id` = {$RefID} LIMIT 1;";
						$Result_SelectReferrer = doquery($Query_SelectReferrer, 'users', true);
						if($Result_SelectReferrer['id'] > 0)
						{
							$UserIPs['r'] = trim($_SERVER['REMOTE_ADDR']);
							$UserIPs['p'] = preg_replace('#[^a-zA-Z0-9\.\,\:\ ]{1,}#si', '', trim($_SERVER['HTTP_X_FORWARDED_FOR']));
							if(empty($UserIPs['p']))
							{
								unset($UserIPs['p']);
							}
							foreach($UserIPs as $Key => $Data)
							{
								$CreateRegIP[] = "{$Key},{$Data}";
								$UserIPs[$Key] = "'{$Data}'";
							}
							$CreateRegIP = implode(';', $CreateRegIP);

							$Query_InsertRefData_Matches = 'null';

							$Query_SelectIPMatches = "SELECT `ID` FROM {{table}} WHERE `Type` = 'ip' AND `Value` IN (".implode(',', $UserIPs).");";
							$Result_SelectIPMatches = doquery($Query_SelectIPMatches, 'used_ip_and_ua');
							if(mysql_num_rows($Result_SelectIPMatches) > 0)
							{
								while($FetchData = mysql_fetch_assoc($Result_SelectIPMatches))
								{
									$MatchedIPIDs[] = $FetchData['ID'];
								}

								$Query_SelectEnterLogMatches = "SELECT `ID` FROM {{table}} WHERE `IP_ID` IN (".implode(',', $MatchedIPIDs).");";
								$Result_SelectEnterLogMatches = doquery($Query_SelectEnterLogMatches, 'user_enterlog');
								if(mysql_num_rows($Result_SelectEnterLogMatches) > 0)
								{
									while($FetchData = mysql_fetch_assoc($Result_SelectEnterLogMatches))
									{
										$MatchedEnterLogIDs[] = $FetchData['ID'];
									}

									$Query_InsertRefData_Matches = '\''.implode(',', $MatchedEnterLogIDs).'\'';
								}
							}
							
							$Query_InsertRefData = '';
							$Query_InsertRefData .= "INSERT INTO {{table}} SET ";
							$Query_InsertRefData .= "`referrer_id` = {$RefID}, ";
							$Query_InsertRefData .= "`newuser_id` = {$UserID}, ";
							$Query_InsertRefData .= "`time` = {$Now}, ";
							$Query_InsertRefData .= "`reg_ip` = '{$CreateRegIP}', ";
							$Query_InsertRefData .= "`matches_found` = {$Query_InsertRefData_Matches};";
							doquery($Query_InsertRefData, 'referring_table');

							$Message = false;
							$Message['msg_id'] = '038';
							$Message['args'] = array('');
							$Message = json_encode($Message);

							SendSimpleMessage($RefID, 0, $Now, 70, '007', '016', $Message);

							$Query_UpdateUser_Fields[] = "`referred` = {$RefID}";
						}
					}
				}

				$ActivationCode = md5(mt_rand(0, 99999999999));

				$Query_UpdateUser_Fields[] = "`id_planet` = {$PlanetID}";
				$Query_UpdateUser_Fields[] = "`settings_mainPlanetID` = {$PlanetID}";
				$Query_UpdateUser_Fields[] = "`current_planet` = {$PlanetID}";
				$Query_UpdateUser_Fields[] = "`galaxy` = {$Galaxy}";
				$Query_UpdateUser_Fields[] = "`system` = {$System}";
				$Query_UpdateUser_Fields[] = "`planet` = {$Planet}";
				if(REGISTER_REQUIRE_EMAILCONFIRM)
				{
					$Query_UpdateUser_Fields[] = "`activation_code` = '{$ActivationCode}'";
				}
				else
				{
					$Query_UpdateUser_Fields[] = "`activation_code` = ''";
				}
				
				$Query_UpdateUser = '';
				$Query_UpdateUser .= "UPDATE {{table}} SET ";
				$Query_UpdateUser .= implode(', ', $Query_UpdateUser_Fields);
				$Query_UpdateUser .= " WHERE `id` = {$UserID} LIMIT 1;";
				doquery($Query_UpdateUser, 'users');

				// Send a invitation private msg
				$Message = false;
				$Message['msg_id'] = '022';
				$Message['args'] = array('');
				$Message = json_encode($Message);

				SendSimpleMessage($UserID, 0, $Now, 70, '004', '009', $Message);
				
				if(REGISTER_REQUIRE_EMAILCONFIRM)
				{
					include($_EnginePath.'includes/functions/SendMail.php');				
					$MailParse['login'] = $Username;
					$MailParse['password'] = $Password;
					$MailParse['Universe'] = $_Lang['RegMail_UniName'];
					$MailParse['activationlink'] = '<a href="'.GAMEURL.'activate.php?code='.$ActivationCode.'">'.GAMEURL.'activate.php?code='.$ActivationCode.'</a>';
					$MailParse['UserRefLink'] = '<a href="'.GAMEURL.'index.php?r='.$UserID.'">'.GAMEURL.'index.php?r='.$UserID.'</a>';
					$MailParsed = parsetemplate($_Lang['mail_template'], $MailParse);
					SendMail($Email, $_Lang['mail_title'], $MailParsed);
				}
				
				if(SERVER_MAINOPEN_TSTAMP <= $Now)
				{
					if(LOCALHOST)
					{
						require($_EnginePath.'config.localhost.php');
					}
					else if(TESTSERVER)
					{
						require($_EnginePath.'config.testserver.php');
					}
					else
					{
						require($_EnginePath.'config.php');
					}
					$cookie = $UserID.'/%/'.$Username.'/%/'.md5(md5($Password).'--'.$__ServerConnectionSettings['secretword']).'/%/0';				
					$JSONResponse['Code'] = 1;
					$JSONResponse['Cookie'][] = array('Name' => $_GameConfig['COOKIE_NAME'], 'Value' => $cookie);
					$JSONResponse['Redirect'] = GAMEURL_UNISTRICT.'/overview.php';
				}
				else
				{
					$JSONResponse['Code'] = 2;
				}
			}
			else
			{
				$JSONResponse['Errors'][] = 15;
				$JSONResponse['BadFields'][] = 'email';
			}
		}
		die('regCallback('.json_encode($JSONResponse).');');
	}
	else
	{
		header('Location: index.php');
		die('regCallback({});');
	}

?>