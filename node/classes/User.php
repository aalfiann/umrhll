<?php
/**
 * This class is a part of reSlim project
 * @author M ABD AZIZ ALFIAN <github.com/aalfiann>
 *
 * Don't remove this class unless You know what to do
 *
 */
namespace classes;
use \classes\Auth as Auth;
use \classes\JSON as JSON;
use \classes\Validation as Validation;
use PDO;
	/**
     * A class for user management in reSlim
     *
     * @package    Core reSlim
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2016 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim/blob/master/license.md  MIT License
     */
	class User {

		protected $db;
		
		// model data user
		var $username,$password,$fullname,$address,$phone,$email,$aboutme,$avatar,$role,$status,$token,$tokentodelete,$passKey,$domain,$apikey,$year;
		
		// for change password
		var $newPassword;

		// for pagination
		var $page,$itemsPerPage;

		// for search
		var $search;

		// for multi language
		var $lang;

		function __construct($db=null) {
			if (!empty($db)) 
	        {
    	        $this->db = $db;
        	}
		}

		/**
		 * Inserting into database to register user
		 * @param safely Will reject register as superuser and admin. Default is false.
		 * @return result process in json encoded data
		 */
		private function doRegister($safely=false){
			
			$newusername = strtolower($this->username);
			$newemail = strtolower(filter_var($this->email,FILTER_SANITIZE_EMAIL));
			$hash = Auth::hashPassword($newusername, $this->password);
			$newrole = Validation::integerOnly($this->role);
			
			if ($safely){
				if ($this->role < 3){
					return $data = [
							'status' => 'error',
							'code' => 'RS901',
							'message' => CustomHandlers::getreSlimMessage('RS901',$this->lang)
						];
				}
			}

			try {
				$this->db->beginTransaction();
				$sql = "INSERT INTO user_data (Username,Password,Fullname,Address,Phone,Email,Aboutme,Avatar,RoleID,StatusID) 
					VALUES (:username,:password,:fullname,:address,:phone,:email,:aboutme,:avatar,:role,'1');";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					$stmt->bindParam(':password', $hash, PDO::PARAM_STR);
					$stmt->bindParam(':fullname', $this->fullname, PDO::PARAM_STR);
					$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
					$stmt->bindParam(':phone', $this->phone, PDO::PARAM_STR);
					$stmt->bindParam(':email', $newemail, PDO::PARAM_STR);
					$stmt->bindParam(':aboutme', $this->aboutme, PDO::PARAM_STR);
					$stmt->bindParam(':avatar', $this->avatar, PDO::PARAM_STR);
					$stmt->bindParam(':role', $newrole, PDO::PARAM_STR);
					if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS101',
							'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang)
						];	
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS901',
							'message' => CustomHandlers::getreSlimMessage('RS901',$this->lang)
						];
					}
				$this->db->commit();
			} catch (PDOException $e) {
				$data = [
					'status' => 'error',
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				];
				$this->db->rollBack();
			}
			return $data;
			$this->db = null;
		}

		/**
		 * Update user
		 * @return result process in json encoded data
		 */
		private function doUpdate(){
			
			$newusername = strtolower($this->username);
			$newemail = strtolower(filter_var($this->email,FILTER_SANITIZE_EMAIL));

			try {
				$this->db->beginTransaction();
				$sql = "UPDATE user_data 
					SET Fullname=:fullname,Address=:address,Phone=:phone,Email=:email,Aboutme=:aboutme,Avatar=:avatar,
					RoleID=:role,StatusID=:status  
					WHERE Username=:username;";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					$stmt->bindParam(':fullname', $this->fullname, PDO::PARAM_STR);
					$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
					$stmt->bindParam(':phone', $this->phone, PDO::PARAM_STR);
					$stmt->bindParam(':email', $newemail, PDO::PARAM_STR);
					$stmt->bindParam(':aboutme', $this->aboutme, PDO::PARAM_STR);
					$stmt->bindParam(':avatar', $this->avatar, PDO::PARAM_STR);
					$stmt->bindParam(':role', $this->role, PDO::PARAM_STR);
					$stmt->bindParam(':status', $this->status, PDO::PARAM_STR);
					if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS103',
							'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
						];	
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS904',
							'message' => CustomHandlers::getreSlimMessage('RS904',$this->lang)
						];
					}
				$this->db->commit();
			} catch (PDOException $e) {
				$data = [
					'status' => 'error',
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				];
				$this->db->rollBack();
			}
			return $data;
			$this->db = null;
		}

		/**
		 * Delete user
		 * @return result process in json encoded data
		 */
		private function doDelete(){
			$newusername = strtolower($this->username);
			
			try {
				$this->db->beginTransaction();
				$sql = "DELETE FROM user_data WHERE Username=:username;";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS104',
							'message' => CustomHandlers::getreSlimMessage('RS104',$this->lang)
						];	
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS905',
							'message' => CustomHandlers::getreSlimMessage('RS905',$this->lang)
						];
					}
				$this->db->commit();
			} catch (PDOException $e) {
				$data = [
					'status' => 'error',
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				];
				$this->db->rollBack();
			}
			return $data;
			$this->db = null;
		}

		/**
		 * Reset Password
		 * @return result process in json encoded data
		 */
		private function doResetPassword(){
			$newusername = strtolower($this->username);
			$hash = Auth::hashPassword($newusername, $this->newPassword);
			
			try {
				$this->db->beginTransaction();
				$sql = "UPDATE user_data a SET a.Password=:newpassword WHERE Username=:username;";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					$stmt->bindParam(':newpassword', $hash, PDO::PARAM_STR);
					if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS103',
							'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
						];	
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS905',
							'message' => CustomHandlers::getreSlimMessage('RS905',$this->lang)
						];
					}
				$this->db->commit();
			} catch (PDOException $e) {
				$data = [
					'status' => 'error',
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				];
				$this->db->rollBack();
			}
			return $data;
			$this->db = null;
		}

		/**
		 * Change Password
		 * @return result process in json encoded data
		 */
		private function doChangePassword(){
			$newusername = strtolower($this->username);
			$hash = Auth::hashPassword($newusername, $this->newPassword);
			if ($this->isPasswordMatch()){
				try {
					$this->db->beginTransaction();
					$sql = "UPDATE user_data a SET a.Password=:newpassword WHERE Username=:username;";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					$stmt->bindParam(':newpassword', $hash, PDO::PARAM_STR);
					if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS103',
							'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
						];	
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS905',
							'message' => CustomHandlers::getreSlimMessage('RS905',$this->lang)
						];
					}
					$this->db->commit();
				} catch (PDOException $e) {
					$data = [
						'status' => 'error',
						'code' => $e->getCode(),
						'message' => $e->getMessage()
					];
					$this->db->rollBack();
				}
			} else {
				$data = [
					'status' => 'error',
					'code' => 'RS903',
					'message' => CustomHandlers::getreSlimMessage('RS903',$this->lang)
				];
			}
			
			return $data;
			$this->db = null;
		}

		/** 
         * Generate encoded pass key when user forgot password
         *
         * @return json encoded data
         */
        public function generatePassKey(){
			if ($this->isEmailRegistered() == true){
				try {
					$newemail = strtolower(filter_var($this->email,FILTER_SANITIZE_EMAIL));
	                $hash = Auth::EncodeAPIKey($newemail.'::'.date("Y-m-d H:i:s"));
    	            $this->db->beginTransaction();
			    	$sql = "INSERT INTO user_forgot (Email,Verifylink,Created,Expired) 
    					VALUES (:email,:verifylink,current_timestamp,date_add(current_timestamp, interval 3 day));";
	    			$stmt = $this->db->prepare($sql);
			   		$stmt->bindParam(':email', $newemail, PDO::PARAM_STR);
		    		$stmt->bindParam(':verifylink', $hash, PDO::PARAM_STR);
			    	if ($stmt->execute()) {
						$data = [
		    				'status' => 'success',
			   				'code' => 'RS101',
			    			'passkey' => $hash,
							'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang),
							'info' => CustomHandlers::getreSlimMessage('CN001',$this->lang)
				    	];
    				} else {
	    				$data = [
		    				'status' => 'error',
			   				'code' => 'RS201',
			    			'message' => CustomHandlers::getreSlimMessage('RS201',$this->lang)
				    	];
				    }
    				$this->db->commit();
		    	} catch (PDOException $e) {
			    	$data = [
						'status' => 'error',
					    'code' => $e->getCode(),
					    'message' => $e->getMessage()
					];
		    		$this->db->rollBack();
		    	}
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS914',
					'message' => CustomHandlers::getreSlimMessage('RS914',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
        }

		/** 
         * Verify pass key and reset password
         *
         * @return json encoded data
         */
        public function verifyPassKey(){
			$hash = Auth::decodeAPIKey($this->passKey);
			$hashed = explode('::',$hash);
			$sqluser = "SELECT b.Username from user_forgot a inner join user_data b on a.Email=b.Email where a.Email=:email;";
			$stmtuser = $this->db->prepare($sqluser);
			$stmtuser->bindParam(':email', $hashed[0], PDO::PARAM_STR);
			if ($stmtuser->execute()){
				if ($stmtuser->rowCount() > 0){
					$single = $stmtuser->fetch();
					$user = $single['Username'];
					try {
						$hashpass = Auth::hashPassword($user,$this->newPassword);
	    	            $this->db->beginTransaction();
				    	$sql = "update user_forgot a 
							inner join user_data b on a.Email = b.Email
							set b.`Password` = :password,a.Expired=current_timestamp
							where b.Email=:email and a.Verifylink=:verifylink and a.Expired > current_timestamp;";
	    				$stmt = $this->db->prepare($sql);
				   		$stmt->bindParam(':email', $hashed[0], PDO::PARAM_STR);
			    		$stmt->bindParam(':password', $hashpass, PDO::PARAM_STR);
						$stmt->bindParam(':verifylink', $this->passKey, PDO::PARAM_STR);
				    	if ($stmt->execute()) {
							if ($stmt->rowCount() > 0){
								$data = [
		    						'status' => 'success',
			   						'code' => 'RS103',
									'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
						    	];
							} else {
								$data = [
		    						'status' => 'error',
			   						'code' => 'RS915',
									'message' => CustomHandlers::getreSlimMessage('RS915',$this->lang)
					    		];
							}
							
    					} else {
	    					$data = [
		    					'status' => 'error',
			   					'code' => 'RS201',
			    				'message' => CustomHandlers::getreSlimMessage('RS201',$this->lang)
				    		];
					    }
    					$this->db->commit();
						Auth::clearUserToken($this->db,$user);
			    	} catch (PDOException $e) {
				    	$data = [
							'status' => 'error',
						    'code' => $e->getCode(),
					    	'message' => $e->getMessage()
						];
			    		$this->db->rollBack();
			    	}
				} else {
					$data = [
		    					'status' => 'error',
			   					'code' => 'RS801',
			    				'message' => CustomHandlers::getreSlimMessage('RS801',$this->lang)
				    		];
				}
			} else {
				$data = [
		    		'status' => 'error',
			   		'code' => 'RS202',
			    	'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
				];
			}	
		    return JSON::encode($data,true);
    		$this->db = null;
        }

		/**
		 * Determine if user is already registered or not
		 * @return boolean true / false
		 */
		public function isRegistered(){
			$newusername = strtolower($this->username);
			$r = false;
			$sql = "SELECT a.Username
				FROM user_data a 
				WHERE a.Username = :username;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
			if ($stmt->execute()) {	
            	if ($stmt->rowCount() > 0){
	                $r = true;
    	        }          	   	
			} 		
			return $r;
			$this->db = null;
		}

		/**
		 * Determine if email is already registered or not
		 * @return boolean true / false
		 */
		public function isEmailRegistered(){
			$newemail = strtolower(filter_var($this->email,FILTER_SANITIZE_EMAIL));
			$r = false;
			$sql = "SELECT a.Email
				FROM user_data a 
				WHERE a.Email = :email;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':email', $newemail, PDO::PARAM_STR);
			if ($stmt->execute()) {	
            	if ($stmt->rowCount() > 0){
					$single = $stmt->fetch();
					    if ((!empty($single['Email'])) || $single['Email'] != null ){
                            $r = true;
                        }
    	        }          	   	
			} 		
			return $r;
			$this->db = null;
		}

		/**
		 * Determine if email is old or not
		 * @return boolean true / false
		 */
		private function isOldEmail(){
			$newusername = strtolower($this->username);
			$newemail = strtolower(filter_var($this->email,FILTER_SANITIZE_EMAIL));
			$r = false;
			$sql = "SELECT a.Username,a.Email
				FROM user_data a 
				WHERE a.Email = :email and a.Username = :username;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
			$stmt->bindParam(':email', $newemail, PDO::PARAM_STR);
			if ($stmt->execute()) {	
            	if ($stmt->rowCount() > 0){
	                $single = $stmt->fetch();
					if ((!empty($single['Email'])) || $single['Email'] != null ){
                        $r = true;
                    }
    	        }          	   	
			} 		
			return $r;
			$this->db = null;
		}

		/**
		 * Determine if user is active or not
		 * @return boolean true / false
		 */
		private function isActivated(){
			$r = false;
			$sql = "SELECT a.StatusID
				FROM user_data a 
				WHERE a.StatusID = '1' AND a.Username = :username;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
			if ($stmt->execute()) {	
            	if ($stmt->rowCount() > 0){
	                $r = true;
    	        }          	   	
			} 		
			return $r;
			$this->db = null;
		}

		/**
		 * Determine if password is match
		 * @return boolean true / false
		 */
		private function isPasswordMatch(){
			$match = false;
			$sql = "SELECT a.Password
				FROM user_data a 
				WHERE a.Username = :username;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
			if ($stmt->execute()){
				if ($stmt->rowCount() > 0){
					$single = $stmt->fetch();
					if (Auth::verifyPassword($this->username, $this->password, $single['Password'])){
						$match = true;
					}
				}
			}
			return $match;
			$this->db = null;
		}

		/** 
         * Generate api key
         *
         * @return json encoded data
         */
		public function generateApiKey(){
			if (Auth::validToken($this->db,$this->token)){
				$data = Auth::generateApiKey($this->db,$this->domain,$this->username);
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS404',
					'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
		}

		/** 
         * Update api key
         *
         * @return json encoded data
         */
		public function updateApiKey(){
			if (Auth::validToken($this->db,$this->token)){
				$data = Auth::updateApiKey($this->db,$this->username,$this->apikey,$this->status);
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS404',
					'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
		}

		/** 
         * Delete api key
         *
         * @return json encoded data
         */
		public function deleteApiKey(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$data = Auth::clearApiKey($this->db,$this->apikey);
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS404',
					'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
		}

		/** 
		 * Search all data user api key paginated
		 * @return result process in json encoded data
		 */
		public function searchAllApiKeysAsPagination() {
			if (Auth::validToken($this->db,$this->token)){
				$roles = Auth::getRoleID($this->db,$this->token);
				$newusername = strtolower($this->username);
				$search = "%$this->search%";
				//count total row
				$sqlcountrow = "SELECT count(a.Domain) as TotalRow 
					from user_api a
					inner join core_status b on a.StatusID=b.StatusID
					where ".(($roles != '1')?'a.Username=:username and ':'')."a.Domain like :search
					order by a.Created_at desc;";
				$stmt = $this->db->prepare($sqlcountrow);		
				if($roles != '1') $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
				$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				
				if ($stmt->execute()) {	
    	    		if ($stmt->rowCount() > 0){
						$single = $stmt->fetch();
						
						// Paginate won't work if page and items per page is negative.
						// So make sure that page and items per page is always return minimum zero number.
						$newpage = Validation::integerOnly($this->page);
						$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
						$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
						$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);

							// Query Data
							$sql = "SELECT a.Created_at,a.Domain,a.ApiKey,a.StatusID,b.`Status`,a.Username,a.Updated_at,a.Updated_by 
								from user_api a
								inner join core_status b on a.StatusID=b.StatusID
								where ".(($roles != '1')?'a.Username=:username and ':'')."a.Domain like :search
								order by a.Created_at desc LIMIT :limpage , :offpage;";
							$stmt2 = $this->db->prepare($sql);
							if($roles != '1') $stmt2->bindParam(':username', $newusername, PDO::PARAM_STR);
							$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
							$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
							$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						
							if ($stmt2->execute()){
								$pagination = new \classes\Pagination();
								$pagination->lang = $this->lang;
								$pagination->totalRow = $single['TotalRow'];
								$pagination->page = $this->page;
								$pagination->itemsPerPage = $this->itemsPerPage;
								$pagination->fetchAllAssoc = $stmt2->fetchAll(PDO::FETCH_ASSOC);
								$data = $pagination->toDataArray();
							} else {
								$data = [
        	    		    		'status' => 'error',
		    	    		    	'code' => 'RS202',
	        			    	    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
								];	
							}			
				    } else {
    	    			$data = [
        	    			'status' => 'error',
		    	    		'code' => 'RS601',
        			    	'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
		    	    }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
				
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Role User
		 * @return result process in json encoded data
		 */
		public function showOptionRole() {
			if (Auth::validToken($this->db,$this->token)){
				if (Auth::getRoleID($this->db,$this->token) == '1'){
					$sql = "SELECT a.RoleID,a.Role
					FROM user_role a
					ORDER BY a.Role ASC;";
				} else {
					$sql = "SELECT a.RoleID,a.Role
					FROM user_role a
					WHERE a.RoleID <> '1'
					ORDER BY a.Role ASC;";
				}
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
        	   		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'result' => $results, 
    	    		        'status' => 'success', 
			           	    'code' => 'RS501',
        		        	'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
			        } else {
        			    $data = [
            		    	'status' => 'error',
		        		    'code' => 'RS601',
        		    	    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
	    	        }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Status User
		 * @return result process in json encoded data
		 */
		public function showOptionStatus() {
			if (Auth::validToken($this->db,$this->token)){
				$sql = "SELECT a.StatusID,a.Status
					FROM core_status a
					WHERE a.StatusID = '1' OR a.StatusID = '42'
					ORDER BY a.Status ASC";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
        	   		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'result' => $results, 
    	    		        'status' => 'success', 
			           	    'code' => 'RS501',
        		        	'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
			        } else {
        			    $data = [
            		    	'status' => 'error',
		        		    'code' => 'RS601',
        		    	    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
	    	        }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}
	
		/** 
		 * Get all data user
		 * @return result process in json encoded data
		 */
		public function showAll() {
			if (Auth::validToken($this->db,$this->token)){
				$roles = Auth::getRoleID($this->db,$this->token); 
				if ($roles == '3'){
					$data = [
    	    			'status' => 'error',
						'code' => 'RS404',
	        	    	'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				} else {
					if ($roles == '1'){
						$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
								a.Created_at, a.Updated_at
							FROM user_data a 
							INNER JOIN user_role b ON a.RoleID = b.RoleID
							INNER JOIN core_status c ON a.StatusID = c.StatusID
							ORDER BY a.Fullname ASC;";
					} else if ($roles == '2'){
						$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
								a.Created_at, a.Updated_at
							FROM user_data a 
							INNER JOIN user_role b ON a.RoleID = b.RoleID
							INNER JOIN core_status c ON a.StatusID = c.StatusID
							WHERE a.RoleID <> '1' AND a.RoleID <> '2'
							UNION
							SELECT b.Username, b.Fullname, b.Address, b.Phone, b.Email, b.Aboutme,b.Avatar, c.Role , d.Status,
								b.Created_at, b.Updated_at
							FROM user_auth a 
							INNER JOIN user_data b ON a.Username = b.Username
							INNER JOIN user_role c ON b.RoleID = c.RoleID
							INNER JOIN core_status d ON b.StatusID = d.StatusID
							WHERE a.RS_Token=:token
							ORDER BY Fullname ASC;";
					}
				
					$stmt = $this->db->prepare($sql);		
					$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);

					if ($stmt->execute()) {	
    		    	    if ($stmt->rowCount() > 0){
        		   		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
							$data = [
			   	    	        'result' => $results, 
    	    		    	    'status' => 'success', 
			           	    	'code' => 'RS501',
	        		        	'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
							];
				        } else {
        				    $data = [
            			    	'status' => 'error',
		        			    'code' => 'RS601',
        		    		    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
							];
		    	        }          	   	
					} else {
						$data = [
    	    				'status' => 'error',
							'code' => 'RS202',
	        		    	'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
						];
					}
				}
				
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data user paginated
		 * @return result process in json encoded data
		 */
		public function showAllAsPagination() {
			if (Auth::validToken($this->db,$this->token)){
				$roles = Auth::getRoleID($this->db,$this->token); 
				if ($roles == '3'){
					$data = [
		    			'status' => 'error',
						'code' => 'RS404',
        		    	'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				} else {
					//Query to count row for superuser and admin
					if ($roles == '1'){
						$sqlcountrow = "SELECT count(a.Username) AS TotalRow
							FROM user_data a 
							INNER JOIN user_role b ON a.RoleID = b.RoleID
							INNER JOIN core_status c ON a.StatusID = c.StatusID
							ORDER BY a.Fullname ASC;";
						$stmt = $this->db->prepare($sqlcountrow);		
					} else if ($roles == '2'){
						$sqlcountrow = "SELECT sum(x.TotalRow) as TotalRow FROM
							(
								SELECT count(a.Username) as TotalRow
								FROM user_data a 
								INNER JOIN user_role b ON a.RoleID = b.RoleID
								INNER JOIN core_status c ON a.StatusID = c.StatusID
								WHERE a.RoleID <> '1' AND a.RoleID <> '2'
								UNION
								SELECT count(b.Username) as TotalRow
								FROM user_auth a 
								INNER JOIN user_data b ON a.Username = b.Username
								INNER JOIN user_role c ON b.RoleID = c.RoleID
								INNER JOIN core_status d ON b.StatusID = d.StatusID
								WHERE a.RS_Token=:token
							) x";
						$stmt = $this->db->prepare($sqlcountrow);		
						$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
					}
				
					if ($stmt->execute()) {	
    	    	    	if ($stmt->rowCount() > 0){
							$single = $stmt->fetch();
						
							// Paginate won't work if page and items per page is negative.
							// So make sure that page and items per page is always return minimum zero number.
							$newpage = Validation::integerOnly($this->page);
							$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
							$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
							$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);

							// Query Data
							if ($roles == '1'){
								$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
									a.Created_at, a.Updated_at
								FROM user_data a 
								INNER JOIN user_role b ON a.RoleID = b.RoleID
								INNER JOIN core_status c ON a.StatusID = c.StatusID
								ORDER BY a.Fullname ASC LIMIT :limpage , :offpage;";
								$stmt2 = $this->db->prepare($sql);
								$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
								$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
							} else if ($roles == '2'){
								$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
									a.Created_at, a.Updated_at
								FROM user_data a 
								INNER JOIN user_role b ON a.RoleID = b.RoleID
								INNER JOIN core_status c ON a.StatusID = c.StatusID
								WHERE a.RoleID <> '1' AND a.RoleID <> '2'
								UNION
								SELECT b.Username, b.Fullname, b.Address, b.Phone, b.Email, b.Aboutme,b.Avatar, c.Role , d.Status,
									b.Created_at, b.Updated_at
								FROM user_auth a 
								INNER JOIN user_data b ON a.Username = b.Username
								INNER JOIN user_role c ON b.RoleID = c.RoleID
								INNER JOIN core_status d ON b.StatusID = d.StatusID
								WHERE a.RS_Token = :token
								ORDER BY Fullname ASC LIMIT :limpage , :offpage;";
								$stmt2 = $this->db->prepare($sql);
								$stmt2->bindParam(':token', $this->token, PDO::PARAM_STR);
								$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
								$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
							}
						
							if ($stmt2->execute()){
								$pagination = new \classes\Pagination();
								$pagination->lang = $this->lang;
								$pagination->totalRow = $single['TotalRow'];
								$pagination->page = $this->page;
								$pagination->itemsPerPage = $this->itemsPerPage;
								$pagination->fetchAllAssoc = $stmt2->fetchAll(PDO::FETCH_ASSOC);
								$data = $pagination->toDataArray();
							} else {
								$data = [
        	    		    		'status' => 'error',
		    	    		    	'code' => 'RS202',
	        			    	    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
								];	
							}			
				        } else {
    	    			    $data = [
        	    		    	'status' => 'error',
		    	    		    'code' => 'RS601',
        			    	    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
							];
		    	        }          	   	
					} else {
						$data = [
    	    				'status' => 'error',
							'code' => 'RS202',
	        			    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
						];
					}	
				}
				
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}

		/** 
		 * Search all data user paginated
		 * @return result process in json encoded data
		 */
		public function searchAllAsPagination() { 
			if (Auth::validToken($this->db,$this->token)){
				$search = "%$this->search%";
				//Query to count row for superuser and admin
				$roles = Auth::getRoleID($this->db,$this->token);
				if ($roles == '1'){
					$sqlcountrow = "SELECT count(a.Username) AS TotalRow
						FROM user_data a 
						INNER JOIN user_role b ON a.RoleID = b.RoleID
						INNER JOIN core_status c ON a.StatusID = c.StatusID
						WHERE a.Fullname like :search OR a.Username like :search
						ORDER BY a.Fullname ASC;";
					$stmt = $this->db->prepare($sqlcountrow);		
					$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				} else {
					$sqlcountrow = "SELECT sum(x.TotalRow) as TotalRow FROM
						(
							SELECT count(a.Username) as TotalRow
							FROM user_data a 
							INNER JOIN user_role b ON a.RoleID = b.RoleID
							INNER JOIN core_status c ON a.StatusID = c.StatusID
							WHERE a.RoleID <> '1' AND a.RoleID <> '2'
							AND a.Fullname like :search 
							OR a.RoleID <> '1' AND a.RoleID <> '2'
							AND a.Username like :search
							UNION
							SELECT count(b.Username) as TotalRow
							FROM user_auth a 
							INNER JOIN user_data b ON a.Username = b.Username
							INNER JOIN user_role c ON b.RoleID = c.RoleID
							INNER JOIN core_status d ON b.StatusID = d.StatusID
							WHERE a.RS_Token=:token 
							AND b.Fullname like :search 
							OR a.RS_Token=:token
							AND b.Username like :search
						) x";
					$stmt = $this->db->prepare($sqlcountrow);		
					$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
					$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				}			

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
						$single = $stmt->fetch();
						
						// Paginate won't work if page and items per page is negative.
						// So make sure that page and items per page is always return minimum zero number.
						$newpage = Validation::integerOnly($this->page);
						$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
						$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
						$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);

						// Query Data
						if ($roles == '1'){
							$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
								a.Created_at, a.Updated_at
							FROM user_data a 
							INNER JOIN user_role b ON a.RoleID = b.RoleID
							INNER JOIN core_status c ON a.StatusID = c.StatusID
							WHERE a.Fullname like :search OR a.Username like :search
							ORDER BY a.Fullname ASC LIMIT :limpage , :offpage;";
							$stmt2 = $this->db->prepare($sql);
							$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
							$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
							$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						} else {
							$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
								a.Created_at, a.Updated_at
							FROM user_data a 
							INNER JOIN user_role b ON a.RoleID = b.RoleID
							INNER JOIN core_status c ON a.StatusID = c.StatusID
							WHERE a.RoleID <> '1' AND a.RoleID <> '2'
							AND a.Fullname like :search 
							OR a.RoleID <> '1' AND a.RoleID <> '2'
							AND a.Username like :search
							UNION
							SELECT b.Username, b.Fullname, b.Address, b.Phone, b.Email, b.Aboutme,b.Avatar, c.Role , d.Status,
								b.Created_at, b.Updated_at
							FROM user_auth a 
							INNER JOIN user_data b ON a.Username = b.Username
							INNER JOIN user_role c ON b.RoleID = c.RoleID
							INNER JOIN core_status d ON b.StatusID = d.StatusID
							WHERE a.RS_Token = :token 
							AND b.Fullname like :search 
							OR a.RS_Token = :token
							AND b.Username like :search
							ORDER BY Fullname ASC LIMIT :limpage , :offpage;";
							$stmt2 = $this->db->prepare($sql);
							$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
							$stmt2->bindParam(':token', $this->token, PDO::PARAM_STR);
							$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
							$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						}
						
						if ($stmt2->execute()){
							$pagination = new \classes\Pagination();
							$pagination->lang = $this->lang;
							$pagination->totalRow = $single['TotalRow'];
							$pagination->page = $this->page;
							$pagination->itemsPerPage = $this->itemsPerPage;
							$pagination->fetchAllAssoc = $stmt2->fetchAll(PDO::FETCH_ASSOC);
							$data = $pagination->toDataArray();
						} else {
							$data = [
            		    		'status' => 'error',
		        		    	'code' => 'RS202',
	        		    	    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
							];	
						}			
			        } else {
        			    $data = [
            		    	'status' => 'error',
		        		    'code' => 'RS601',
        		    	    'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
	    	        }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}


		/** 
		 * Get data single user
		 * @return result process in json encoded data
		 */
		public function showUser() {
			if (Auth::validToken($this->db,$this->token)){
				$roles = Auth::getRoleID($this->db,$this->token);
				$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
						a.Created_at, a.Updated_at
					FROM user_data a 
					INNER JOIN user_role b ON a.RoleID = b.RoleID
					INNER JOIN core_status c ON a.StatusID = c.StatusID
					WHERE a.Username = :username ".($roles == '1'?'':"AND a.StatusID = '1'").";";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    		if ($stmt->rowCount() > 0){
        			   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'result' => $results, 
		    		        'status' => 'success', 
				           	'code' => 'RS501',
        			        'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
				    } else {
    	    			$data = [
        	    		   	'status' => 'error',
		    	    	    'code' => 'RS601',
        			        'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
		    	    }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        	    	'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}		
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}
			
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get data single user
		 * @return result process in json encoded data
		 */
		public function showUserPublic() {
			$sql = "SELECT a.Username, a.Fullname, a.Address, a.Phone, a.Email, a.Aboutme,a.Avatar, b.Role , c.Status,
						a.Created_at, a.Updated_at
					FROM user_data a 
					INNER JOIN user_role b ON a.RoleID = b.RoleID
					INNER JOIN core_status c ON a.StatusID = c.StatusID
					WHERE a.Username = :username AND a.StatusID = '1';";
				
			$stmt = $this->db->prepare($sql);		
			$stmt->bindParam(':username', $this->username, PDO::PARAM_STR);

			if ($stmt->execute()) {	
    	    	if ($stmt->rowCount() > 0){
        		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$data = [
		   	            'result' => $results, 
	    		        'status' => 'success', 
			           	'code' => 'RS501',
        		        'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
					];
			    } else {
        			$data = [
            		   	'status' => 'error',
		        	    'code' => 'RS601',
        		        'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
					];
	    	    }          	   	
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS202',
	        	    'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
				];
			}		
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}

		/** 
		 * Regiter new user
		 * @return result process in json encoded data
		 */
		public function register(){
			if (Auth::validToken($this->db,$this->token)){
				if ( preg_match('/[A-Za-z0-9]+/',$this->username) == false ){
					$data = [
						'status' => 'error',
						'code' => 'RS804',
						'message' => CustomHandlers::getreSlimMessage('RS804',$this->lang)
					];
				} else {
					if ($this->isRegistered() == false){
						if ($this->isEmailRegistered() == false){
							$data = $this->doRegister();
						} else {
							$data = [
								'status' => 'error',
								'code' => 'RS914',
								'message' => CustomHandlers::getreSlimMessage('RS914',$this->lang)
							];
						}
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS902',
							'message' => CustomHandlers::getreSlimMessage('RS902',$this->lang)
						];
					}
				}
			} else {
				$data = [
					'status' => 'error',
					'code' => 'RS401',
					'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}
			
			return JSON::encode($data,true);
		}

		/** 
		 * Regiter new user safely
		 * @return result process in json encoded data
		 */
		public function registerSafely(){
			if ( preg_match('/[A-Za-z0-9]+/',$this->username) == false ){
				$data = [
					'status' => 'error',
					'code' => 'RS804',
					'message' => CustomHandlers::getreSlimMessage('RS804',$this->lang)
				];
			} else {
				if ($this->isRegistered() == false){
					if ($this->isEmailRegistered() == false){
						$data = $this->doRegister(true);
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS914',
							'message' => CustomHandlers::getreSlimMessage('RS914',$this->lang)
						];
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS902',
						'message' => CustomHandlers::getreSlimMessage('RS902',$this->lang)
					];
				}
			}
			
			return JSON::encode($data,true);
		}

		/** 
		 * Login user
		 * @return result process in json encoded data
		 */
		public function login(){
			if ( preg_match('/[A-Za-z0-9]+/',$this->username) == false ){
				$data = [
					'status' => 'error',
					'code' => 'RS804',
					'message' => CustomHandlers::getreSlimMessage('RS804',$this->lang)
				];
			} else {
				if ($this->isRegistered()){
					if ($this->isActivated()) {
						if ($this->isPasswordMatch()){
							$data = Auth::generateToken($this->db,$this->username);
						} else {
							$data = [
								'status' => 'error',
								'code' => 'RS903',
								'message' => CustomHandlers::getreSlimMessage('RS903',$this->lang)
							];
						}
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS906',
							'message' => CustomHandlers::getreSlimMessage('RS906',$this->lang)
						];
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS902',
						'message' => CustomHandlers::getreSlimMessage('RS902',$this->lang)
					];
				}
			}
		
			return JSON::encode($data,true);
		}

		/** 
		 * Logout user
		 * @return result process in json encoded data
		 */
		public function logout(){
			$data = Auth::clearToken($this->db,$this->username,$this->token);
			return JSON::encode($data,true);
		}

		/** 
		 * Update user
		 * @return result process in json encoded data
		 */
		public function update(){
			if (Auth::validToken($this->db,$this->token)){
				if ($this->isOldEmail() == true){
					$data = $this->doUpdate();
				} else {
					if ($this->isEmailRegistered() == false) {
						$data = $this->doUpdate();
					} else {
						$data = [
	    					'status' => 'error',
							'code' => 'RS914',
        	    			'message' => CustomHandlers::getreSlimMessage('RS914',$this->lang)
				];
					}
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}
			return JSON::encode($data,true);
		}

		/** 
		 * Delete user
		 * @return result process in json encoded data
		 */
		public function delete(){
			if (Auth::validToken($this->db,$this->token)){
				$roles = Auth::getRoleID($this->db,$this->token);
				if ($roles == '1'){
					if ($this->isRegistered()){
						$data = $this->doDelete();
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS902',
							'message' => CustomHandlers::getreSlimMessage('RS902',$this->lang)
						];
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];	
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}
			return JSON::encode($data,true);
		}

		/** 
		 * Change Password
		 * @return result process in json encoded data
		 */
		public function changePassword(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				if ($this->isRegistered()){
					$data = $this->doChangePassword();
					Auth::clearUserToken($this->db,$this->username);
				} else {
					$data = [
	    				'status' => 'error',
						'code' => 'RS907',
	        	    	'message' => CustomHandlers::getreSlimMessage('RS907',$this->lang)
					];
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}
			return JSON::encode($data,true);
		}

		/** 
		 * Reset Password
		 * @return result process in json encoded data
		 */
		public function resetPassword(){
			if (Auth::validToken($this->db,$this->token)){
				if ($this->isRegistered()){
					$data = $this->doResetPassword();
					Auth::clearUserToken($this->db,$this->username);
				} else {
					$data = [
	    				'status' => 'error',
						'code' => 'RS907',
	        	    	'message' => CustomHandlers::getreSlimMessage('RS907',$this->lang)
					];
				}
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}
			return JSON::encode($data,true);
		}

		/** 
		 * Verify Token
		 * @return result process in json encoded data
		 */
		public function verifyToken(){
			if (Auth::validToken($this->db,$this->token)){
				$data = [
	    			'status' => 'success',
					'code' => 'RS304',
	        	    'message' => CustomHandlers::getreSlimMessage('RS304',$this->lang)
				];
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS404',
	            	'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}
			return JSON::encode($data,true);
		}

		/** 
		 * Get Role User by Token
		 * @return result process in json encoded data
		 */
		public function getRole(){
			if (!empty(Auth::getRoleID($this->db,$this->token))){
				$data = [
	    			'status' => 'success',
					'role' => Auth::getRoleID($this->db,$this->token),
					'code' => 'RS304',
	        	    'message' => CustomHandlers::getreSlimMessage('RS304',$this->lang)
				];
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}
			return JSON::encode($data,true);
		}

		/** 
         * Get all data token user
         *
         * @return json encoded data
         */
		public function getUserDataToken(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$data = Auth::getDataToken($this->db,$this->username);
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS404',
					'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
		}
		
		/** 
         * Delete single token
         *
         * @return json encoded data
         */
		public function deleteSingleToken(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$data = Auth::clearSingleToken($this->db,$this->username,$this->tokentodelete);
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS404',
					'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
		}

		/** 
         * Delete all user token except active one
         *
         * @return json encoded data
         */
		public function deleteAllUserToken(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$data = Auth::clearSafeUserToken($this->db,$this->username,$this->token);
			} else {
				$data = [
	    			'status' => 'error',
				    'code' => 'RS404',
					'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
    			];
			}
		    return JSON::encode($data,true);
    		$this->db = null;
		}


		/** 
		 * Get data statistic user
		 * @return result process in json encoded data
		 */
		public function statUserSummary() {
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1' || $roles == '2'){
					$sql = "SELECT
						(SELECT count(x.UserID) FROM user_data x WHERE x.StatusID='1') AS 'Active',
						(SELECT count(x.UserID) FROM user_data x WHERE x.StatusID='42') AS 'Suspended',
						(SELECT count(x.UserID) FROM user_data x) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Suspended))/(SELECT Total))*100),0) AS 'Percent_Up',
						IFNULL((100 - (SELECT Percent_Up)),0) as 'Percent_Down';";
					$stmt = $this->db->prepare($sql);

					if ($stmt->execute()) {	
    		    		if ($stmt->rowCount() > 0){
        				   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
							$data = [
			   		            'result' => $results, 
		    			        'status' => 'success', 
				        	   	'code' => 'RS501',
        			        	'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
							];
					    } else {
    		    			$data = [
        		    		   	'status' => 'error',
		    		    	    'code' => 'RS601',
        				        'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
							];
			    	    }          	   	
					} else {
						$data = [
    	    				'status' => 'error',
							'code' => 'RS202',
	        	    		'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
						];
					}	
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				}	
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}
			
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get data statistic data api
		 * @return result process in json encoded data
		 */
		public function statAPISummary() {
			if (Auth::validToken($this->db,$this->token)){
				$newusername = strtolower($this->username);
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1'){
					$sql = "SELECT
						(SELECT count(x.Domain) FROM user_api x WHERE x.StatusID='1') AS 'Active',
						(SELECT count(x.Domain) FROM user_api x WHERE x.StatusID='42') AS 'Suspended',
						(SELECT count(x.Domain) FROM user_api x) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Suspended))/(SELECT Total))*100),0) AS 'Percent_Up',
						IFNULL((100 - (SELECT Percent_Up)),0) AS 'Percent_Down';";
					$stmt = $this->db->prepare($sql);
				} else {
					$sql = "SELECT
						(SELECT count(x.Domain) FROM user_api x WHERE x.StatusID='1' AND x.Username=:username) AS 'Active',
						(SELECT count(x.Domain) FROM user_api x WHERE x.StatusID='42' AND x.Username=:username) AS 'Suspended',
						(SELECT count(x.Domain) FROM user_api x WHERE x.Username=:username) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Suspended))/(SELECT Total))*100),0) AS 'Percent_Up',
						IFNULL((100 - (SELECT Percent_Up)),0) AS 'Percent_Down';";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
				}

				if ($stmt->execute()) {	
    	    		if ($stmt->rowCount() > 0){
        			   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'result' => $results, 
		    		        'status' => 'success', 
				           	'code' => 'RS501',
        			        'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
				    } else {
    	    			$data = [
        	    		   	'status' => 'error',
		    	    	    'code' => 'RS601',
        			        'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
		    	    }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        	    	'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}		
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}
			
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get data statistic user uploaded file
		 * @return result process in json encoded data
		 */
		public function statUploadSummary() {
			if (Auth::validToken($this->db,$this->token)){
				$newusername = strtolower($this->username);
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1' || $roles == '2'){
					$sql = "SELECT 
						(SELECT count(x.ItemID) FROM user_upload x WHERE x.StatusID='49') AS 'Public',
						(SELECT count(x.ItemID) FROM user_upload x WHERE x.StatusID='50') AS 'Private',
						(SELECT count(x.ItemID) FROM user_upload x) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Private))/(SELECT Total))*100),0) AS 'Percent_Up',
						IFNULL((100 - (SELECT Percent_Up)),0) AS 'Percent_Down';";
					$stmt = $this->db->prepare($sql);	
				} else {
					$sql = "SELECT 
						(SELECT count(x.ItemID) FROM user_upload x WHERE x.StatusID='49' AND x.Username=:username) AS 'Public',
						(SELECT count(x.ItemID) FROM user_upload x WHERE x.StatusID='50' AND x.Username=:username) AS 'Private',
						(SELECT count(x.ItemID) FROM user_upload x WHERE x.Username=:username) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Private))/(SELECT Total))*100),0) AS 'Percent_Up',
						IFNULL((100 - (SELECT Percent_Up)),0) AS 'Percent_Down';";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
				}
				
				if ($stmt->execute()) {	
    	    		if ($stmt->rowCount() > 0){
        			   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'result' => $results, 
		    		        'status' => 'success', 
				           	'code' => 'RS501',
        			        'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
				    } else {
    	    			$data = [
        	    		   	'status' => 'error',
		    	    	    'code' => 'RS601',
        			        'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
		    	    }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        	    	'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}
			
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get data statistic user in Year
		 * @return result process in json encoded data
		 */
        public function statUserYear(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1' || $roles == '2'){
					$newyear = Validation::integerOnly($this->year);
					$sql = "SELECT 
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 1 GROUP BY MONTH(a.Created_at)) AS 'Jan',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 2 GROUP BY MONTH(a.Created_at)) AS 'Feb',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 3 GROUP BY MONTH(a.Created_at)) AS 'Mar',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 4 GROUP BY MONTH(a.Created_at)) AS 'Apr',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 5 GROUP BY MONTH(a.Created_at)) AS 'May',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 6 GROUP BY MONTH(a.Created_at)) AS 'Jun',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 7 GROUP BY MONTH(a.Created_at)) AS 'Jul',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 8 GROUP BY MONTH(a.Created_at)) AS 'Aug',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 9 GROUP BY MONTH(a.Created_at)) AS 'Sep',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 10 GROUP BY MONTH(a.Created_at)) AS 'Oct',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 11 GROUP BY MONTH(a.Created_at)) AS 'Nov',
						(SELECT count(a.UserID) AS Total FROM user_data a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 12 GROUP BY MONTH(a.Created_at)) AS 'Dec';";
					$stmt = $this->db->prepare($sql);		
					$stmt->bindParam(':newyear', $newyear, PDO::PARAM_STR);

					if ($stmt->execute()) {	
						if ($stmt->rowCount() > 0){
							$datares = "";
							$datalabel = '{"labels":["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],';
							$dataseries = '"series":[';
							while($redata = $stmt->fetch()) {
								$datares .= '
									['.JSON::safeEncode($redata['Jan']).','.JSON::safeEncode($redata['Feb']).','.JSON::safeEncode($redata['Mar']).','.JSON::safeEncode($redata['Apr']).','.JSON::safeEncode($redata['May']).','.JSON::safeEncode($redata['Jun']).','.JSON::safeEncode($redata['Jul']).','.JSON::safeEncode($redata['Aug']).','.JSON::safeEncode($redata['Sep']).','.JSON::safeEncode($redata['Oct']).','.JSON::safeEncode($redata['Nov']).','.JSON::safeEncode($redata['Dec']).'],';
							}
							$datares = substr($datares, 0, -1);
							$combine = $datalabel.$dataseries.$datares.']}';
							$data = [
								'results' => json_decode($combine), 
								'status' => 'success', 
								'code' => 'RS501',
								'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
							];
						} else {
							$data = [
								'status' => 'error',
								'code' => 'RS601',
								'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
							];
						}          	   	
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS202',
							'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
						];
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
					];
				}
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}	
	
			return JSON::encode($data,true);
			$this->db= null;
		}

		/** 
		 * Get data statistic user generate API in Year
		 * @return result process in json encoded data
		 */
        public function statAPIYear(){
			if (Auth::validToken($this->db,$this->token)){
				$newyear = Validation::integerOnly($this->year);
				$newusername = strtolower($this->username);
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1'){
					$sql = "SELECT 
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 1 GROUP BY MONTH(a.Created_at)) AS 'Jan',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 2 GROUP BY MONTH(a.Created_at)) AS 'Feb',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 3 GROUP BY MONTH(a.Created_at)) AS 'Mar',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 4 GROUP BY MONTH(a.Created_at)) AS 'Apr',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 5 GROUP BY MONTH(a.Created_at)) AS 'May',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 6 GROUP BY MONTH(a.Created_at)) AS 'Jun',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 7 GROUP BY MONTH(a.Created_at)) AS 'Jul',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 8 GROUP BY MONTH(a.Created_at)) AS 'Aug',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 9 GROUP BY MONTH(a.Created_at)) AS 'Sep',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 10 GROUP BY MONTH(a.Created_at)) AS 'Oct',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 11 GROUP BY MONTH(a.Created_at)) AS 'Nov',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 12 GROUP BY MONTH(a.Created_at)) AS 'Dec';";
					$stmt = $this->db->prepare($sql);		
					$stmt->bindParam(':newyear', $newyear, PDO::PARAM_STR);
				} else {
					$sql = "SELECT 
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 1 GROUP BY MONTH(a.Created_at)) AS 'Jan',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 2 GROUP BY MONTH(a.Created_at)) AS 'Feb',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 3 GROUP BY MONTH(a.Created_at)) AS 'Mar',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 4 GROUP BY MONTH(a.Created_at)) AS 'Apr',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 5 GROUP BY MONTH(a.Created_at)) AS 'May',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 6 GROUP BY MONTH(a.Created_at)) AS 'Jun',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 7 GROUP BY MONTH(a.Created_at)) AS 'Jul',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 8 GROUP BY MONTH(a.Created_at)) AS 'Aug',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 9 GROUP BY MONTH(a.Created_at)) AS 'Sep',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 10 GROUP BY MONTH(a.Created_at)) AS 'Oct',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 11 GROUP BY MONTH(a.Created_at)) AS 'Nov',
						(SELECT count(a.Domain) AS Total FROM user_api a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 12 GROUP BY MONTH(a.Created_at)) AS 'Dec';";
					$stmt = $this->db->prepare($sql);		
					$stmt->bindParam(':newyear', $newyear, PDO::PARAM_STR);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
				}
				

				if ($stmt->execute()) {	
					if ($stmt->rowCount() > 0){
						$datares = "";
						$datalabel = '{"labels":["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],';
						$dataseries = '"series":[';
						while($redata = $stmt->fetch()) {
							$datares .= '
								['.JSON::safeEncode($redata['Jan']).','.JSON::safeEncode($redata['Feb']).','.JSON::safeEncode($redata['Mar']).','.JSON::safeEncode($redata['Apr']).','.JSON::safeEncode($redata['May']).','.JSON::safeEncode($redata['Jun']).','.JSON::safeEncode($redata['Jul']).','.JSON::safeEncode($redata['Aug']).','.JSON::safeEncode($redata['Sep']).','.JSON::safeEncode($redata['Oct']).','.JSON::safeEncode($redata['Nov']).','.JSON::safeEncode($redata['Dec']).'],';
						}
						$datares = substr($datares, 0, -1);
						$combine = $datalabel.$dataseries.$datares.']}';
						$data = [
							'results' => json_decode($combine), 
							'status' => 'success', 
							'code' => 'RS501',
							'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS601',
							'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
					}          	   	
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS202',
						'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}	
	
			return JSON::encode($data,true);
			$this->db= null;
		}

		/** 
		 * Get data statistic user uploaded file in Year
		 * @return result process in json encoded data
		 */
        public function statUploadYear(){
			if (Auth::validToken($this->db,$this->token)){
				$newyear = Validation::integerOnly($this->year);
				$newusername = strtolower($this->username);
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1' || $roles == '2'){
					$sql = "SELECT 
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 1 GROUP BY MONTH(a.Date_Upload)) AS 'Jan',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 2 GROUP BY MONTH(a.Date_Upload)) AS 'Feb',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 3 GROUP BY MONTH(a.Date_Upload)) AS 'Mar',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 4 GROUP BY MONTH(a.Date_Upload)) AS 'Apr',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 5 GROUP BY MONTH(a.Date_Upload)) AS 'May',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 6 GROUP BY MONTH(a.Date_Upload)) AS 'Jun',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 7 GROUP BY MONTH(a.Date_Upload)) AS 'Jul',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 8 GROUP BY MONTH(a.Date_Upload)) AS 'Aug',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 9 GROUP BY MONTH(a.Date_Upload)) AS 'Sep',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 10 GROUP BY MONTH(a.Date_Upload)) AS 'Oct',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 11 GROUP BY MONTH(a.Date_Upload)) AS 'Nov',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 12 GROUP BY MONTH(a.Date_Upload)) AS 'Dec';";
					$stmt = $this->db->prepare($sql);		
					$stmt->bindParam(':newyear', $newyear, PDO::PARAM_STR);
				} else {
					$sql = "SELECT 
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 1 GROUP BY MONTH(a.Date_Upload)) AS 'Jan',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 2 GROUP BY MONTH(a.Date_Upload)) AS 'Feb',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 3 GROUP BY MONTH(a.Date_Upload)) AS 'Mar',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 4 GROUP BY MONTH(a.Date_Upload)) AS 'Apr',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 5 GROUP BY MONTH(a.Date_Upload)) AS 'May',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 6 GROUP BY MONTH(a.Date_Upload)) AS 'Jun',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 7 GROUP BY MONTH(a.Date_Upload)) AS 'Jul',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 8 GROUP BY MONTH(a.Date_Upload)) AS 'Aug',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 9 GROUP BY MONTH(a.Date_Upload)) AS 'Sep',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 10 GROUP BY MONTH(a.Date_Upload)) AS 'Oct',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 11 GROUP BY MONTH(a.Date_Upload)) AS 'Nov',
						(SELECT count(a.ItemID) AS Total FROM user_upload a WHERE a.Username=:username AND YEAR(a.Date_Upload) = :newyear AND MONTH(a.Date_Upload) = 12 GROUP BY MONTH(a.Date_Upload)) AS 'Dec';";
					$stmt = $this->db->prepare($sql);		
					$stmt->bindParam(':newyear', $newyear, PDO::PARAM_STR);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
				}

				if ($stmt->execute()) {	
					if ($stmt->rowCount() > 0){
						$datares = "";
						$datalabel = '{"labels":["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],';
						$dataseries = '"series":[';
						while($redata = $stmt->fetch()) {
							$datares .= '
								['.JSON::safeEncode($redata['Jan']).','.JSON::safeEncode($redata['Feb']).','.JSON::safeEncode($redata['Mar']).','.JSON::safeEncode($redata['Apr']).','.JSON::safeEncode($redata['May']).','.JSON::safeEncode($redata['Jun']).','.JSON::safeEncode($redata['Jul']).','.JSON::safeEncode($redata['Aug']).','.JSON::safeEncode($redata['Sep']).','.JSON::safeEncode($redata['Oct']).','.JSON::safeEncode($redata['Nov']).','.JSON::safeEncode($redata['Dec']).'],';
						}
						$datares = substr($datares, 0, -1);
						$combine = $datalabel.$dataseries.$datares.']}';
						$data = [
							'results' => json_decode($combine), 
							'status' => 'success', 
							'code' => 'RS501',
							'message' => CustomHandlers::getreSlimMessage('RS501',$this->lang)
						];
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS601',
							'message' => CustomHandlers::getreSlimMessage('RS601',$this->lang)
						];
					}          	   	
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS202',
						'message' => CustomHandlers::getreSlimMessage('RS202',$this->lang)
					];
				}
			} else {
				$data = [
    	    		'status' => 'error',
					'code' => 'RS404',
	        	    'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
				];
			}	
	
			return JSON::encode($data,true);
			$this->db= null;
		}
	}