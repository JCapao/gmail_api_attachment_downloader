<?php //Basilla Edzel T., Capao, Jireh September 10, 2019
	require __DIR__ . '/vendor/autoload.php';
	// set_time_limit(500);
	// 4/qwEOdaaVpu_ubzYpmAdZrznWOOaJiealPT2eVY9kY8ceQhcou_zkIw0
	
	// if (php_sapi_name() != 'cli') {
		// throw new Exception('This application must be run on the command line.');
	// }

	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	function getClient() {
		$client = new Google_Client();
		$client->setApplicationName('Gmail API PHP Quickstart');
		$client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
		$client->setScopes(Google_Service_Gmail::GMAIL_MODIFY);
		$client->setAuthConfig('credentials.json');
		$client->setAccessType('offline');
		$client->setPrompt('select_account consent');

		// Load previously authorized token from a file, if it exists.
		// The file token.json stores the user's access and refresh tokens, and is
		// created automatically when the authorization flow completes for the first
		// time.
		$tokenPath = 'token.json';
		if (file_exists($tokenPath)) {
			$accessToken = json_decode(file_get_contents($tokenPath), true);
			$client->setAccessToken($accessToken);
		}

		// If there is no previous token or it's expired.
		if ($client->isAccessTokenExpired()) {
			// Refresh the token if possible, else fetch a new one.
			if ($client->getRefreshToken()) {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			} else {
				// Request authorization from the user.
				$authUrl = $client->createAuthUrl();
				printf("Open the following link in your browser:\n%s\n", $authUrl);
				print 'Enter verification code: ';
				$authCode = trim(fgets(STDIN));
				// $authCode = "4/qgHlzVSv0So00cSkH66EbdGgypniMgljxuXyEtcdiBxKN0CJRc8PW6M";

				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
				$client->setAccessToken($accessToken);

				// Check to see if there was an error.
				if (array_key_exists('error', $accessToken)) {
					throw new Exception(join(', ', $accessToken));
				}
			}
			// Save the token to a file.
			if (!file_exists(dirname($tokenPath))) {
				mkdir(dirname($tokenPath), 0700, true);
			}
			file_put_contents($tokenPath, json_encode($client->getAccessToken()));
		}
		return $client;
	}
	 
	function getLabels() {	
		// Get the API client and construct the service object.
		$client = getClient();
		$service = new Google_Service_Gmail($client);

		// Print the labels in the user's account.
		$user = 'me';
		$results = $service->users_labels->listUsersLabels($user);

		if (count($results->getLabels()) == 0) {
			print "No labels found.\n<br>";
		} else {
			print "Labels:\n<br>";

			foreach ($results->getLabels() as $label) {
				printf("- %s\n<br>", $label->getName()); 
			}
			
			echo count($results->getLabels()) . " total labels.\n "; 
		} 
	}
	 
	function listMessages() {
		$client = getClient();
		$service = new Google_Service_Gmail($client);
		$userId = "me";
	 
		$pageToken = NULL;
		$messages = array();
		$opt_param = array(); 
		
		// $j=0;
		do {
		
			// if($j==1) break; 
			try {
				if ($pageToken) {
					$opt_param['pageToken'] = $pageToken;
				}
				
				$messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param); 
				
				if ($messagesResponse->getMessages()) {
					$messages = array_merge($messages, $messagesResponse->getMessages());
					$pageToken = $messagesResponse->getNextPageToken();
				}
				
			} catch (Exception $e) {
				print 'An error occurred: ' . $e->getMessage();
			}
			// $j++;
		} while ($pageToken);
 
		$i=0;
		foreach ($messages as $message) {  
			$msg = $service->users_messages->get($userId, $message->getId()); 
			
			print  "\n<br><br>" . $i .'.)  Message with ID: ' . $message->getId() . "\n";  
			echo "<pre>" . var_export($msg,true) . "</pre>";
			// print '<br> Subject: ' . $message->getSnippet()  . "\n"; // NO DISPLAY
			// print '<br> Payload: ' . $message->getPayload()  . "\n"; // NO DISPLAY
			
			// echo "<pre>" . var_export($msg->labelIds,true) . "</pre>";
			// echo "<pre> Subject: " . var_export($msg->snippet,true) . "</pre>";
			// echo "<pre> Payload: " . var_export($msg->payload->parts[1],true) . "</pre>"; 
			$i++;	 
		}
	  
		return $messages;
		// return $msg;
	} 
	
	function countUnread($labelIds) {  
		$client = getClient();
		$service = new Google_Service_Gmail($client); 
		$userId = 'me';   
		$pageToken = NULL;
		$messages = array();
		$opt_param = array();
		$opt_param['labelIds'] = $labelIds ;
		 
		do { 
			try {
				if ($pageToken) {
					$opt_param['pageToken'] = $pageToken;
				}
				
				$messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param); 
				
				if ($messagesResponse->getMessages()) {
					$messages = array_merge($messages, $messagesResponse->getMessages());
					$pageToken = $messagesResponse->getNextPageToken();
				}
				
			} catch (Exception $e) {
				print 'An error occurred: ' . $e->getMessage();
			} 
		} while ($pageToken);
	  
		$result = count($messages);
		echo  "<br><br> Total ". $opt_param['labelIds'] . " = ".$result . "\n\n <br>";
	}
	 
	function getUnread($labelIds) {
		$client = getClient();
		$service = new Google_Service_Gmail($client); 
		$service2 = new Google_Service_Gmail_Message(); 
		$userId = 'me';   
		$pageToken = NULL;
		$messages = array();
		$opt_param = array();
		$opt_param['labelIds'] = $labelIds ;
		 
		do { 
			try {
				if ($pageToken) {
					$opt_param['pageToken'] = $pageToken;
				}
				
				$messagesResponse = $service->users_messages->listUsersMessages($userId, $opt_param);  
				if ($messagesResponse->getMessages()) {
					$messages = array_merge($messages, $messagesResponse->getMessages());
					$pageToken = $messagesResponse->getNextPageToken();
				}
				
			} catch (Exception $e) {
				print 'An error occurred: ' . $e->getMessage();
			} 
		} while ($pageToken);
 
		echo "\n\n<br><br> LABEL: " . $labelIds . "\n";
		
		$i=0;
		$j=0;
		foreach ($messages as $message) {   
			$msg = $service->users_messages->get($userId, $message->getId());  
			$messageIds = $msg->id;  
			$subj = var_export($msg->snippet,true) ; 
			$parts = $msg->getPayload()->getParts();  
			if ( ! isset($parts[1])) {
			    $parts[1] = null;
				$fileName = null;
				$fileType = null;
				$attamentID = null;
				$attamentSize = null;
			}else{
				$MIMEtype = $parts[1]['mimeType'];
				$FILEname = $parts[1]['filename'];
				$fileName = var_export($msg->payload->parts[1]->filename,true);
				$fileType = var_export($msg->payload->parts[1]->mimeType,true) ; 
				$attamentID = var_export($msg->payload->parts[1]->body->attachmentId,true); 
				$attamentSize = var_export($msg->payload->parts[1]->body->size,true); 
				$stringAttamentID = str_replace("'", '', $attamentID); 
			}
			
			// $MIMEtype = $parts[1]['mimeType'];
			// $FILEname = $parts[1]['filename'];	 
			// $fileName = var_export($msg->payload->parts[1]->filename,true);
			// $fileType = var_export($msg->payload->parts[1]->mimeType,true) ; 
			// $attamentID = var_export($msg->payload->parts[1]->body->attachmentId,true); 
			// $attamentSize = var_export($msg->payload->parts[1]->body->size,true); 
			// // $attamentID = " ' " . var_export($msg->payload->parts[1]->body->attachmentId,true)." ' "; 
		 
			// print "\n<br><br> MIME Type: " . $msg->getPayload()->getMimeType()  . "\n";   
			// print "\n<br><br> MIME Type: " . $MIMEtype. "\n";    
			// print "\n<br><br> Filename : " . $MIMEtype  . "\n";   
			// echo "<pre>" . var_export($msg,true) . "</pre>";
			
			print  "\n<br><br>" . $i .'.)<br>  Message with ID: ' . $messageIds . "\n<br> ";  
			echo " Subject: " . $subj . "\n<br>";  
			echo " Attachment Id: " . $stringAttamentID . "\n<br>"; 
			echo " Attachment Size: " . $attamentSize . "\n<br>"; 
			echo " MIME Type: " . $MIMEtype . "\n<br>";    
			echo " <b> Filename: " . $FILEname  . "</b>\n<br>";   
				 
			if($MIMEtype == "application/pdf"){ 
				// print  "\n<br><br>" . $i .'.)  Message with ID: ' . $message->getId() . "\n";  
				// print '<br> Subject: ' . $msg->getSnippet()  . "\n";    
				// print '<br> Filetype: ' . $msg->getPayload()->getMimeType()  . "\n";    
				// echo "<pre>" . var_export($msg->labelIds,true) . "</pre>";
				// echo "<pre>" . var_export($msg->labelIds,true) . "</pre>";
				 
				$attachmentObj = $service->users_messages_attachments->get($userId, $messageIds, $stringAttamentID);
				$data = $attachmentObj->getData(); //Get data from attachment object
				$data = strtr($data, array('-' => '+', '_' => '/')); 
				$myfile = fopen("DOWNLOADS/$FILEname.pdf", "w+");;
				fwrite($myfile, base64_decode($data));
				fclose($myfile);

				$mods = new Google_Service_Gmail_ModifyMessageRequest();
				$mods->setRemoveLabelIds(array('UNREAD')); 
				// $service->users_messages->modify($userId, $messageIds, $mods); 
				try {
					$messagessss = $service->users_messages->modify($userId, $messageIds, $mods);
					// return $messagessss;
				} catch (Exception $e) {
					print 'An error occurred: ' . $e->getMessage();
				}
				
				$j++; 
			} else{} 
			$i++;	 
		}
	  
		// return $messages;
		echo "<br><br> \nTotal .pdf of " . $labelIds . ": " . $j ."/".$i;
	}
   
// $labelIds = "UNREAD";
// $labelIds = "CATEGORY_PROMOTIONS";
// $labelIds = "CATEGORY_UPDATES";
// $labelIds = "TRASH";
$labelIds = "SENT";
// $labelIds = "INBOX";
// $labelIds = "in:inbox is:unread";
// $labelIds = "in:inbox is:unread -category:(promotions OR social)";
// $labelIds = "in:inbox is:unread category:primary";
	 
getLabels(); 
// listMessages(); 
getUnread($labelIds); 
// countUnread($labelIds); 
?>