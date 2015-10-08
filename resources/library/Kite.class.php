<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		Kite.class.php
	*/

	$KiteV = '1.0.0';

	class Kite {
		# Redirection function
		function Go($to) {
			header("Location: $to");
		}
		
		# Check that a URL is valid
		function CheckURL($Kite, $Validate) {			
			# Check URL validity
			if(filter_var($Kite, FILTER_VALIDATE_URL)) {
				# Do we have a filter for this URL?
				if(!empty($Validate)) {
					$expFilter = explode(',', $Validate);

					# Check the filter
					foreach($expFilter as $Filter) {
						if(strpos($Kite, $Filter) !== false) {
							return false;
						}
					}
				}
				
				# Otherwise it is OK
				return true;
			}else{
				# Incorrect URL
				return false;
			}
		}
		
		# Is the user logged in?
		function Loggedin() {
			if(isset($_SESSION['KiteLog']) && $_SESSION['KiteLog'] == 1) {
				return 1;
			}else{
				return 0;
			}
		}
		
		# Custom session function
		function KiteSession($timeout = 3600) {
			ini_set('session.gc_maxlifetime', $timeout);
			session_start();

			if(isset($_SESSION['timeout_idle']) && $_SESSION['timeout_idle'] < time()) {
				session_destroy();
				session_start();
				session_regenerate_id();
				$_SESSION = array();
			}

			$_SESSION['timeout_idle'] = time() + $timeout;
		}
		
		function CheckInstall($table) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);

			try {
				$result = $dbh->query("SELECT 1 FROM $table LIMIT 1");
			} catch (Exception $e) {
				# Table not found
				return FALSE;
			}

			# Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
			return $result !== FALSE;
		}
		
		# Get the site settings
		function GetSiteSettings() {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_settings WHERE identifier = 'settings'");
			$stmt->execute();
			$getSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $getSettings;
		}
		
		# Update site settings ( nasty looking :-( )
		function UpdateSettings($POST) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Verify we have all the data
			if(!empty($POST['setTitle']) && 
			   !empty($POST['setSite']) && 
			   !empty($POST['setApi']) && 
			   !empty($POST['setShort']) && 
			   !empty($POST['setPage'])) {
				
				# A little bit of validation (only can be run by admins anyway though)
				if(!is_numeric($POST['setApi']) || !is_numeric($POST['setShort']) || !is_numeric($POST['setPage'])) {
					return 0;
				}
				
				# Update the site settings
				$stmt = $dbh->prepare("UPDATE kt_settings SET website_title = :website_title, website_address = :website_address, api_usage = :api_usage, url_length = :url_length, items_perpage = :items_perpage, filter_urls = :filter_urls WHERE identifier = 'settings'");
				$stmt->bindParam(':website_title', $POST['setTitle']);
				$stmt->bindParam(':website_address', $POST['setSite']);
				$stmt->bindParam(':api_usage', $POST['setApi']);
				$stmt->bindParam(':url_length', $POST['setShort']);
				$stmt->bindParam(':items_perpage', $POST['setPage']);
				$stmt->bindParam(':filter_urls', $POST['setFilter']);
				$stmt->execute();
			}
		}
		
		# Generates the removal hash
		function LinkHash($Kite, $Salt, $Hash='') {
			# If the hash is not empty, we are verifying
			if(!empty($Hash)) {
				$GenHash = md5($Kite);
				$GenHash = sha1(md5($GenHash));
				$GenHash = sha1($Salt . $GenHash);
				
				# Is it correct?
				if($Hash == $GenHash) {
					return 1;
				}else{
					return 0;
				}
			}else{
				# Hash is empty so we are generating
				$GenHash = md5($Kite);
				$GenHash = sha1(md5($GenHash));
				$GenHash = sha1($Salt . $GenHash);
				
				return $GenHash;
			}
		}
		
		# Sets a password for a link
		function SetPassword($Kite, $Password) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Update the row with the Api key
			$stmt = $dbh->prepare("UPDATE kt_shorts SET password = :password WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->bindParam(':password', $Password);
			$stmt->execute();
		}
		
		# Check if there is a password set
		function PasswordSet($Kite) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			$getKite = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			# Is there a password?
			if(!empty($getKite[0]['password'])) {
				return 1;
			}else{
				return 0;
			}
		}
		
		# Verify a password
		function VerifyPassword($Kite, $Password) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			$getKite = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			# Is there a password?
			if($Password == $getKite[0]['password']) {
				return 1;
			}else{
				return 0;
			}
		}
		
		# Register a new user
		function RegisterUser($UserEmail, $UserPass) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_users WHERE email = :email");
			$stmt->bindParam(':email', $UserEmail);
			$stmt->execute();
			$getKiteUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$KiteUserExists = count($getKiteUser);
			
			# Does the user exist already?
			if($KiteUserExists) {
				# Return 0 if the user exists
				return 0;
			}else{
				# Hash the password securely
				$UserPass = password_hash($UserPass, PASSWORD_DEFAULT);
				
				# Get the current timestamp
				$KiteTime = time();
				
				# User does not exist - create them
				$data = array( 'email' => $UserEmail, 'password' => $UserPass, 'joined' => $KiteTime );
				$stmt = $dbh->prepare("INSERT INTO kt_users (email, password, joined) VALUES (:email, :password, :joined)");
				$stmt->execute($data);
				
				# Get the last INSERT ID
				$lastInsert = $dbh->lastInsertId();
				
				# Generate an Api key
				$UserApi = PseudoCrypt::hash($lastInsert, 10);
				$UserApi .= PseudoCrypt::hash($lastInsert*5, 10);
				$UserApi .= PseudoCrypt::hash($lastInsert+5, 10);
				
				# Update the row with the Api key
				$stmt = $dbh->prepare("UPDATE kt_users SET apiKey = :apiKey WHERE id = :id");
				$stmt->bindParam(':apiKey', $UserApi);
				$stmt->bindParam(':id', $lastInsert);
				$stmt->execute();
				
				# Return 1 if the new user is created
				return 1;
			}
		}
		
		# Log in a user
		function LoginUser($UserEmail, $UserPass) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_users WHERE email = :email");
			$stmt->bindParam(':email', $UserEmail);
			$stmt->execute();
			$getKiteUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$KiteUserExists = count($getKiteUser);
			
			# Does the user exist?
			if($KiteUserExists) {
				if(password_verify($UserPass, $getKiteUser[0]['password'])) {
					$_SESSION['KiteLog'] = 1;
					$_SESSION['KiteEmail'] = $UserEmail;
					$_SESSION['KiteUserID'] = $getKiteUser[0]['id'];
					$_SESSION['KiteAccType'] = $getKiteUser[0]['type'];
					return 1;
				}else{
					return 0;
				}
			}else{
				# Return 0 as the user does not exist
				return 0;
			}
		}
		
		# Get user information
		function GetUser($KiteUser) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_users WHERE id = :id");
			$stmt->bindParam(':id', $KiteUser);
			$stmt->execute();
			$getUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $getUser;
		}
		
		# Get user information from an Api key
		function GetUserFromApi($KiteApi) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_users WHERE apiKey = :apiKey");
			$stmt->bindParam(':apiKey', $KiteApi);
			$stmt->execute();
			$getUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $getUser;
		}
		
		# Get the API usage of a user
		function GetApiUsage($KiteUser) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Current time
			$currTime = time();
			
			# Subtract 24 hours from the current time
			$ApiUsage24 = $currTime - 86400;
			
			# Set type to API
			$KiteMethod = 'a';
			
			$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE user_id = :user_id AND timestamp >= :timestamp AND method = :method");
			$stmt->bindParam(':user_id', $KiteUser);
			$stmt->bindParam(':timestamp', $ApiUsage24);
			$stmt->bindParam(':method', $KiteMethod);
			$stmt->execute();
			$getUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$ApiUsage = count($getUsage);
			
			return $ApiUsage;
		}
		
		# Get Kite information from an ID
		function GetKite($Kite, $KiteUser) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE id = :id AND user_id = :user_id");
			$stmt->bindParam(':id', $Kite);
			$stmt->bindParam(':user_id', $KiteUser);
			$stmt->execute();
			$getKite = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$KiteExists = count($getKite);
			
			# Does not exist?
			if(!$KiteExists) {
				return 0;
			}
			
			return $getKite;
		}
		
		# Select Kites by a certain user
		function GetKites($KiteUser, $Page, $PerPage, $SearchTerm='') {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Init variable
			$Other = 0;
			
			# If page is anything other than 1, work out what we need
			if($Page != 1) {
				$Other = ($Page * $PerPage - $PerPage);
			}
			
			# Prepare the format
			if(!empty($SearchTerm)) {
				$SearchTerm = '%'.$SearchTerm.'%';
			}
			
			# A users or all?
			if(!$KiteUser) {
				# Do we have a search term?
				if(!empty($SearchTerm)) {
					$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE url LIKE :searchTerm OR hash LIKE :searchTerm ORDER by id DESC LIMIT $Other, $PerPage");
					$stmt->bindParam(':searchTerm', $SearchTerm);
				}else{
					$stmt = $dbh->prepare("SELECT * FROM kt_shorts ORDER by id DESC LIMIT $Other, $PerPage");
				}
				//$stmt->bindParam(':user_id', $KiteUser);
				$stmt->execute();
			}else{
				# Do we have a search term?
				if(!empty($SearchTerm)) {					
					$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE url LIKE :searchTerm OR hash LIKE :searchTerm AND user_id = :user_id ORDER by id DESC LIMIT $Other, $PerPage");
					$stmt->bindParam(':searchTerm', $SearchTerm);
				}else{
					$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE user_id = :user_id ORDER by timestamp DESC LIMIT $Other, $PerPage");
				}
				$stmt->bindParam(':user_id', $KiteUser);
				$stmt->execute();
			}
			$getKites = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $getKites;
		}
		
		# Get user list for the admin panel
		function GetUserList($Page, $PerPage) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Init variable
			$Other = 0;
			
			# If page is anything other than 1, work out what we need
			if($Page != 1) {
				$Other = ($Page * $PerPage - $PerPage);
			}
			
			$stmt = $dbh->prepare("SELECT * FROM kt_users ORDER by id DESC LIMIT $Other, $PerPage");
			$stmt->execute();
			$getUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $getUsers;
		}
		
		# Set user level
		function SetUserLevel($UserID, $UserLvl) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("UPDATE kt_users SET type = :type WHERE id = :id");
			$stmt->bindParam(':id', $UserID);
			$stmt->bindParam(':type', $UserLvl);
			$stmt->execute();
		}
		
		# Get the amount of users
		function GetUserCount() {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_users");
			$stmt->execute();
			
			$getCount = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$UserCount = count($getCount);
			
			return $UserCount;
		}
		
		# Get the amount of shorts
		function GetKiteCount() {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_shorts");
			$stmt->execute();
			
			$getCount = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$KiteCount = count($getCount);
			
			return $KiteCount;
		}
		
		# Check the API key is valid
		function CheckApiKey($KiteAPI) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * FROM kt_users WHERE apiKey = :apiKey");
			$stmt->bindParam(':apiKey', $KiteAPI);
			$stmt->execute();
			$getKiteApi = $stmt->fetchAll(PDO::FETCH_ASSOC);

			# This will return 1 if the user has been found
			$KiteApiExists = count($getKiteApi);
			
			# If it exists return 1
			if($KiteApiExists) {
				return 1;
			}else{
				return 0;
			}
		}
		
		# Generate a Kite
		function GenerateKite($Kite, $KiteLength, $KiteMethod='d', $KiteApi='0') {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Timestamp
			$tsKite = time();
			
			# Direct or Api?
			if($KiteMethod == 'd') {
				# Check if the user is logged in
				if(isset($_SESSION['KiteUserID'])) {
					$UserID = $_SESSION['KiteUserID'];
				}else{
					$UserID = 0;
				}
			}else{
				# Get user by Api
				$stmt = $dbh->prepare("SELECT * FROM kt_users WHERE apiKey = :apiKey");
				$stmt->bindParam(':apiKey', $KiteApi);
				$stmt->execute();
				$getKiteUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
				# Set variable
				$UserID = $getKiteUser[0]['id'];
			}
			
			$data = array( 'url' => $Kite, 'timestamp' => $tsKite, 'user_id' => $UserID, 'method' => $KiteMethod );
			$stmt = $dbh->prepare("INSERT INTO kt_shorts (url, timestamp, user_id, method) VALUES (:url, :timestamp, :user_id, :method)");
			$stmt->execute($data);
			
			# Get the last INSERT ID
			$lastInsert = $dbh->lastInsertId();
			
			# Generate the hash based on the ID
			$KiteHash = PseudoCrypt::hash($lastInsert, $KiteLength);
			
			# Protect against using the same short
			$KiteCounter = 10;
			
			# Loop to regenerate a short
			while(Kite::CheckShort($KiteHash)) {
				$KiteHash = PseudoCrypt::hash($lastInsert*$KiteCounter, $KiteLength);
				
				# Increase the counter
				$KiteCounter++;
			}
			
			# Update the row with the hash
			$stmt = $dbh->prepare("UPDATE kt_shorts SET hash = :hash WHERE id = :id");
			$stmt->bindParam(':hash', $KiteHash);
			$stmt->bindParam(':id', $lastInsert);
			$stmt->execute();
			
			return $KiteHash;
		}

		# Remove a URL
		function RemoveKite($Kite) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("DELETE FROM kt_shorts WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			
			$stmt = $dbh->prepare("DELETE FROM kt_views WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
		}
		
		# Get URL information from a hash
		function GetURLFromHash($Kite) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);

			$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			$getKite = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return $getKite;
		}

		# Check if a short exists
		function CheckShort($Kite) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);

			$stmt = $dbh->prepare("SELECT * FROM kt_shorts WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			$getKite = $stmt->fetchAll(PDO::FETCH_ASSOC);

			# This will return 1 if the Kite has been found
			$KiteExists = count($getKite);

			# If it exists return 1
			if($KiteExists) {
				return 1;
			}else{
				return 0;
			}
		}
		
		# Add a view
		function AddView($Kite) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Update the total views
			$stmt = $dbh->prepare("UPDATE kt_shorts SET total_views = total_views+1 WHERE hash = :hash");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			
			# Get the day/month/year
			$dmyKite = date("d-m-y", time());
			
			# Try and find it
			$stmt = $dbh->prepare("SELECT * from kt_views WHERE datetime = :datetime AND hash = :hash");
			$stmt->bindParam(':datetime', $dmyKite);
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			
			$getViews = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			# Does it exist?
			$KiteViews = count($getViews);
			
			if($KiteViews) {
				# Update the view count
				$stmt = $dbh->prepare("UPDATE kt_views SET views = views+1 WHERE datetime = :datetime AND hash = :hash");
				$stmt->bindParam(':datetime', $dmyKite);
				$stmt->bindParam(':hash', $Kite);
				$stmt->execute();
			}else{
				# Insert a new view row
				$data = array( 'hash' => $Kite, 'datetime' => $dmyKite, 'views' => '1' );
				$stmt = $dbh->prepare("INSERT INTO kt_views (hash, datetime, views) VALUES (:hash, :datetime, :views)");
				$stmt->execute($data);
			}
		}
		
		# Function to get data to build the graphs
		function GetKiteViews($Kite) {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			$stmt = $dbh->prepare("SELECT * from kt_views WHERE hash = :hash ORDER BY id DESC LIMIT 14");
			$stmt->bindParam(':hash', $Kite);
			$stmt->execute();
			
			$getViews = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			# Are there any?
			$CountKite = count($getViews);
			
			if(!$CountKite) {
				return 0;
			}
			
			$getViews = array_reverse($getViews);
			
			return $getViews;
		}
		
		# Action logger
		function LogAction($LogType, $LogUser, $Kite='') {
			$dbh = Kite_SQL::kiteConnect(HOST, DBSE, USER, PASS);
			
			# Get the user that actioned
			$stmt = $dbh->prepare("SELECT * FROM kt_users WHERE id = :id");
			$stmt->bindParam(':id', $LogUser);
			$stmt->execute();
			
			# Get details
			$getUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			# Is there a username?
			if(count($getUser) > 0) {
				$User = $getUser[0]['email'];
			}else{
				$User = 'Guest';
			}
			
			# Get the current time
			$cTime = time();
			
			# Switch statement
			switch ($LogType) {
				# Removed success
				case '1':
				$lMessage = 'Short URL (' . $Kite . ') removed at (' . date('H:i:s - d/m/Y', $cTime) . ') by ' . $User;
				break;
					
				# Removed unsuccessful
				case '2':
				$lMessage = 'Short URL (' . $Kite . ') attempted removal at (' . date('H:i:s - d/m/Y', $cTime) . ') by ' . $User;
				break;
			}
					
			# Insert the data
			$data = array( 'username' => $User, 'message' => $lMessage, 'type' => $LogType, 'timestamp' => $cTime );
			$stmt = $dbh->prepare("INSERT INTO kt_log (username, message, type, timestamp) VALUES (:username, :message, :type, :timestamp)");
			$stmt->execute($data);
		}
		
		# Build JSON responses
		function BuildResponse($KiteID, $KiteResponse) {
			$KiteString = array(
				'status' => $KiteID,
				'response' => $KiteResponse
			);
			
			$KiteString = json_encode($KiteString, true);
			
			return $KiteString;
		}
		
		# Small function for the pages
		function KitePage($KitePage) {
			if(file_exists('pages/' . $KitePage . '.php')) {
				echo '<div class="well well-lg white-bg">';
				include 'pages/' . $KitePage . '.php';
				echo '</div>';
			}else{
				echo '<div class="well well-lg white-bg">';
				include 'pages/404.php';
				echo '</div>';
			}
		}
	}

	# SQL
	class Kite_SQL {
		static function kiteConnect($host, $dbse, $user, $pass) {
			$sqlError = 0;
			
			try {
				$dbh = new PDO('mysql:host=' . $host . ';dbname=' . $dbse . '', $user, $pass);
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				return $dbh;
			} catch(PDOException $e) {
				$sqlError = 1;
				$errorLogged = $e->getMessage();
				exit($errorLogged);
			}			
		}
	}
?>