<?php
namespace modules\pages;
use \classes\Auth as Auth;
use \classes\JSON as JSON;
use \classes\Validation as Validation;
use \classes\UniversalCache as UniversalCache;
use \classes\CustomHandlers as CustomHandlers;
use PDO;
	/**
     * A class for pages management
     *
     * @package    modules/pages
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim-modules-pages/blob/master/LICENSE.md  MIT License
     */
    class Pages {
		
		//database var
		protected $db;
		
		//base var
        protected $basepath,$baseurl,$basemod;

        //master var
		var $username,$token,$statusid,$apikey,$adminname,$user;
		
		//data
		var $pageid,$title,$image,$description,$content,$tags,$search,$firstdate,$lastdate,$sort,$year,$limit;

		//for pagination
		var $page,$itemsPerPage;

		//for multi language
		var $lang;

        function __construct($db=null) {
			if (!empty($db)) $this->db = $db;
			$this->baseurl = (($this->isHttps())?'https://':'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
            $this->basepath = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']);
			$this->basemod = dirname(__FILE__);
		}

		//Detect scheme host
        function isHttps() {
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            
            if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                if (!empty($_SERVER['HTTP_CF_VISITOR'])){
                    return isset($_SERVER['HTTPS']) ||
                    ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) &&
                    $visitor->scheme == 'https';
                } else {
                    return isset($_SERVER['HTTPS']);
                }
            } else {
                return 0;
            }            
        }
		
		//Get modules information
        public function viewInfo(){
            return file_get_contents($this->basemod.'/package.json');
        }


        //PAGE=====================================


		/** 
		 * Add new page
		 * @return result process in json encoded data
		 */
        public function addPage(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $newusername = strtolower(filter_var($this->username,FILTER_SANITIZE_STRING));
				$role = Auth::getRoleID($this->db,$this->token);
				if ($role == '1' || $role == '2'){
					$statuscode = '51';
				} else {
					$statuscode = '52';
				}
    		    try {
    				$this->db->beginTransaction();
	    			$sql = "INSERT INTO data_page (Title,Image,Description,Content,Tags,StatusID,Created_at,Username) 
		    			VALUES (:title,:image,:description,:content,:tags,'".$statuscode."',current_timestamp,:username);";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':title', $this->title, PDO::PARAM_STR);
					$stmt->bindParam(':image', $this->image, PDO::PARAM_STR);
					$stmt->bindParam(':description', $this->description, PDO::PARAM_STR);
					$stmt->bindParam(':content', $this->content, PDO::PARAM_STR);
                    $stmt->bindParam(':tags', $this->tags, PDO::PARAM_STR);
                    $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS101',
							'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang)
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
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }

			return JSON::encode($data,true);
			$this->db = null;

        }

		/** 
		 * Update data page
		 * @return result process in json encoded data
		 */
        public function updatePage(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $role = Auth::getRoleID($this->db,$this->token);
				if ($role == '1' || $role == '2'){
                    $newusername = strtolower(filter_var($this->username,FILTER_SANITIZE_STRING));
                    $newpageid = Validation::integerOnly($this->pageid);
                    $newstatusid = Validation::integerOnly($this->statusid);
                    
        			try {
	        			$this->db->beginTransaction();
                        $sql = "UPDATE data_page 
                            SET Title=:title,Image=:image,Description=:description,Content=:content,Tags=:tags,
                                StatusID=:status,Updated_by=:username,
                                Updated_at=current_timestamp
		        		    WHERE PageID=:pageid;";

				    $stmt = $this->db->prepare($sql);
					$stmt->bindParam(':title', $this->title, PDO::PARAM_STR);
					$stmt->bindParam(':image', $this->image, PDO::PARAM_STR);
					$stmt->bindParam(':description', $this->description, PDO::PARAM_STR);
                    $stmt->bindParam(':content', $this->content, PDO::PARAM_STR);
                    $stmt->bindParam(':tags', $this->tags, PDO::PARAM_STR);
                    $stmt->bindParam(':status', $newstatusid, PDO::PARAM_STR);
					$stmt->bindParam(':pageid', $newpageid, PDO::PARAM_STR);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
	    				if ($stmt->execute()) {
		    				$data = [
			    				'status' => 'success',
				    			'code' => 'RS103',
					    		'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
						    ];	
    					} else {
	    					$data = [
		    					'status' => 'error',
			    				'code' => 'RS203',
				    			'message' => CustomHandlers::getreSlimMessage('RS203',$this->lang)
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
			$this->db = null;

		}
		
		/** 
		 * Update data draft page for non superuser or admin
		 * @return result process in json encoded data
		 */
        public function updateDraftPage(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $role = Auth::getRoleID($this->db,$this->token);
                $newusername = strtolower(filter_var($this->username,FILTER_SANITIZE_STRING));
                $newpageid = Validation::integerOnly($this->pageid);
                    
        		try {
	        		$this->db->beginTransaction();
					if ($role > 2){
						$sql = "UPDATE data_page 
                            SET Title=:title,Image=:image,Description=:description,Content=:content,Tags=:tags,
                                StatusID='52',Updated_by=:username,
                                Updated_at=current_timestamp
							WHERE PageID=:pageid AND Username=:username;";
						$stmt = $this->db->prepare($sql);
						$stmt->bindParam(':title', $this->title, PDO::PARAM_STR);
						$stmt->bindParam(':image', $this->image, PDO::PARAM_STR);
						$stmt->bindParam(':description', $this->description, PDO::PARAM_STR);
						$stmt->bindParam(':content', $this->content, PDO::PARAM_STR);
						$stmt->bindParam(':tags', $this->tags, PDO::PARAM_STR);
						$stmt->bindParam(':pageid', $newpageid, PDO::PARAM_STR);
						$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
						if ($stmt->execute()) {
		    				$data = [
			    				'status' => 'success',
				    			'code' => 'RS103',
					    		'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
						    ];	
    					} else {
	    					$data = [
		    					'status' => 'error',
			    				'code' => 'RS203',
				    			'message' => CustomHandlers::getreSlimMessage('RS203',$this->lang)
					    	];
    					}
	    			    $this->db->commit();
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS404',
							'message' => CustomHandlers::getreSlimMessage('RS404',$this->lang)
						];
					}
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
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }

			return JSON::encode($data,true);
			$this->db = null;

        }

        /** 
		 * Update data view page
		 * @return result process in json encoded data
		 */
        public function updateViewPage(){
            $newpageid = Validation::integerOnly($this->pageid);
                    
        		try {
					$this->db->beginTransaction();
					$sql = "UPDATE data_page a SET a.Viewer=a.Viewer+1 where a.PageID=:pageid;";
					$stmt = $this->db->prepare($sql);		
					$stmt->bindParam(':pageid', $newpageid, PDO::PARAM_STR);
	    			if ($stmt->execute()) {
		    			$data = [
			    			'status' => 'success',
							'code' => 'RS103',
				    		'message' => CustomHandlers::getreSlimMessage('RS103',$this->lang)
					    ];	
    				} else {
	    				$data = [
		    				'status' => 'error',
		    				'code' => 'RS203',
			    			'message' => CustomHandlers::getreSlimMessage('RS203',$this->lang)
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

			return JSON::encode($data,true);
			$this->db = null;

        }

		/** 
		 * Delete data page
		 * @return result process in json encoded data
		 */
        public function deletePage(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $role = Auth::getRoleID($this->db,$this->token);
                if ($role == '1' || $role == '2'){
                    $newpageid = Validation::integerOnly($this->pageid);
                    $newusername = strtolower(filter_var($this->username,FILTER_SANITIZE_STRING));
			
    			    try {
                        $this->db->beginTransaction();
                        if ($role == '1') {
                            $sql = "DELETE FROM data_page 
    		    	    		WHERE PageID=:pageid;";
	    		    		$stmt = $this->db->prepare($sql);
                            $stmt->bindParam(':pageid', $newpageid, PDO::PARAM_STR);
                        } else {
                            $sql = "DELETE FROM data_page 
    		    	    		WHERE PageID=:pageid AND Username=:username;";
	    		    		$stmt = $this->db->prepare($sql);
                            $stmt->bindParam(':pageid', $newpageid, PDO::PARAM_STR);
                            $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
                        }
	    	    		
					    if ($stmt->execute()) {
    						$data = [
	    						'status' => 'success',
		    					'code' => 'RS104',
			    				'message' => CustomHandlers::getreSlimMessage('RS104',$this->lang)
				    		];	
					    } else {
    						$data = [
	    						'status' => 'error',
		    					'code' => 'RS204',
			    				'message' => CustomHandlers::getreSlimMessage('RS204',$this->lang)
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
			$this->db = null;

		}

		/** 
		 * Show data page only single detail for registered user
		 * @return result process in json encoded data
		 */
		public function showSinglePage(){
			if (Auth::validToken($this->db,$this->token,$this->username)){
	            $newpageid = Validation::integerOnly($this->pageid);
				
				$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Content,a.Tags,a.Viewer,a.Username,
									a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
								from data_page a
								inner join core_status b on a.StatusID=b.StatusID
								where a.PageID = :pageid LIMIT 1;";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':pageid', $newpageid, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
        	   		   	$datares = "[";
								while($redata = $stmt->fetch()) 
								{
									//Start Tags
									$return_arr = null;
									$names = $redata['Tags'];	
									$named = preg_split( "/[,]/", $names );
									foreach($named as $name){
										if ($name != null){$return_arr[] = utf8_encode(trim($name));}
									}
									//End Tags

									$datares .= '{"PageID":'.JSON::safeEncode($redata['PageID']).',
											"Title":'.JSON::safeEncode($redata['Title']).',
											"Image":'.JSON::safeEncode($redata['Image']).',
											"Description":'.JSON::safeEncode($redata['Description']).',
											"Content":'.JSON::safeEncode($redata['Content']).',
											"Tags_inline":'.JSON::safeEncode($redata['Tags']).',
											"Tags":'.JSON::safeEncode($return_arr).',
											"Viewer":'.JSON::safeEncode($redata['Viewer']).',
											"Created_at":'.JSON::safeEncode($redata['Created_at']).',
											"Username":'.JSON::safeEncode($redata['Username']).',
											"Updated_at":'.JSON::safeEncode($redata['Updated_at']).',
											"Updated_by":'.JSON::safeEncode($redata['Updated_by']).',
											"StatusID":'.JSON::safeEncode($redata['StatusID']).',
											"Status":'.JSON::safeEncode($redata['Status']).'},';
								}
								$datares = substr($datares, 0, -1);
								$datares .= "]";
						$data = [
			   	            'result' => json_decode($datares), 
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
			
			return JSON::encode($data,true);
	        $this->db= null;
		}
		
		/** 
		 * Show data page only single detail for guest without login
		 * @return result process in json encoded data
		 */
		public function showSinglePagePublic(){
            $newpageid = Validation::integerOnly($this->pageid);
				
				$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Content,a.Tags,a.Viewer,a.Username as 'User',
									a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
								from data_page a
								inner join core_status b on a.StatusID=b.StatusID
								where a.StatusID = '51' and a.PageID = :pageid LIMIT 1;";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':pageid', $newpageid, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
        	   		   	$datares = "[";
								while($redata = $stmt->fetch()) 
								{
									//Start Tags
									$return_arr = null;
									$names = $redata['Tags'];	
									$named = preg_split( "/[,]/", $names );
									foreach($named as $name){
										if ($name != null){$return_arr[] = utf8_encode(trim($name));}
									}
									//End Tags

									$datares .= '{"PageID":'.JSON::safeEncode($redata['PageID']).',
											"Title":'.JSON::safeEncode($redata['Title']).',
											"Image":'.JSON::safeEncode($redata['Image']).',
											"Description":'.JSON::safeEncode($redata['Description']).',
											"Content":'.JSON::safeEncode($redata['Content']).',
											"Tags_inline":'.JSON::safeEncode($redata['Tags']).',
											"Tags":'.JSON::safeEncode($return_arr).',
											"Viewer":'.JSON::safeEncode($redata['Viewer']).',
											"Created_at":'.JSON::safeEncode($redata['Created_at']).',
											"User":'.JSON::safeEncode($redata['User']).',
											"Updated_at":'.JSON::safeEncode($redata['Updated_at']).',
											"Updated_by":'.JSON::safeEncode($redata['Updated_by']).',
											"StatusID":'.JSON::safeEncode($redata['StatusID']).',
											"Status":'.JSON::safeEncode($redata['Status']).'},';
								}
								$datares = substr($datares, 0, -1);
								$datares .= "]";
						$data = [
			   	            'result' => json_decode($datares), 
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
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

        /** 
		 * Search all data page paginated
		 * @return result process in json encoded data
		 */
		public function searchPageAsPagination() {
			if (Auth::validToken($this->db,$this->token,$this->username)){
                $role = Auth::getRoleID($this->db,$this->token);
				$newusername = strtolower(filter_var($this->username,FILTER_SANITIZE_STRING));
				$search = "%$this->search%";
				if ($role == '1' || $role == '2'){
					$sqlcountrow = "SELECT count(a.PageID) as TotalRow
						from data_page a
						inner join core_status b on a.StatusID=b.StatusID
						where a.PageID like :search
						or a.Title like :search
						or a.Tags like :search
						or a.Username like :search
						or b.Status like :search
						order by a.Created_at desc;";
					$stmt = $this->db->prepare($sqlcountrow);
					$stmt->bindValue(':search', $search, PDO::PARAM_STR);
				} else {
					$sqlcountrow = "SELECT count(a.PageID) as TotalRow
						from data_page a
						inner join core_status b on a.StatusID=b.StatusID
						where a.Username=:username AND a.PageID like :search
						or a.Username=:username AND a.Title like :search
						or a.Username=:username AND a.Tags like :search
						or a.Username=:username AND b.Status like :search
						order by a.Created_at desc;";
					$stmt = $this->db->prepare($sqlcountrow);
					$stmt->bindValue(':search', $search, PDO::PARAM_STR);
					$stmt->bindValue(':username', $newusername, PDO::PARAM_STR);
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

						if ($role == '1' || $role == '2'){
							// Query Data
							$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Content,a.Tags,a.Viewer,a.Username,
									a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
								from data_page a
								inner join core_status b on a.StatusID=b.StatusID
								where a.PageID like :search
								or a.Title like :search
								or a.Tags like :search
								or a.Username like :search
								or b.Status like :search
								order by a.Created_at desc LIMIT :limpage , :offpage;";
							$stmt2 = $this->db->prepare($sql);
							$stmt2->bindValue(':search', $search, PDO::PARAM_STR);
							$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
							$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						} else {
							// Query Data
							$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Content,a.Tags,a.Viewer,a.Username,
									a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
								from data_page a
								inner join core_status b on a.StatusID=b.StatusID
								where a.Username=:username AND a.PageID like :search
								or a.Username=:username AND a.Title like :search
								or a.Username=:username AND a.Tags like :search
								or a.Username=:username AND b.Status like :search
								order by a.Created_at desc LIMIT :limpage , :offpage;";
							$stmt2 = $this->db->prepare($sql);
							$stmt2->bindValue(':search', $search, PDO::PARAM_STR);
							$stmt2->bindValue(':username', $newusername, PDO::PARAM_STR);
							$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
							$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						}
							
						
						if ($stmt2->execute()){
							if ($stmt2->rowCount() > 0){
								$results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
								$pagination = new \classes\Pagination();
								$pagination->lang = $this->lang;
								$pagination->totalRow = $single['TotalRow'];
								$pagination->page = $this->page;
								$pagination->itemsPerPage = $this->itemsPerPage;
								$pagination->fetchAllAssoc = $results;
								$data = $pagination->toDataArray();
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
		 * Search all data page paginated public
		 * @return result process in json encoded data
		 */
		public function searchPageAsPaginationPublic() {
			$search = "%$this->search%";
			$sqlcountrow = "SELECT count(a.PageID) as TotalRow
					from data_page a
					inner join core_status b on a.StatusID=b.StatusID
					where a.StatusID='51' and a.PageID like :search
					or a.StatusID='51' and a.Title like :search
					or a.StatusID='51' and a.Tags like :search
					or a.StatusID='51' and a.Username like :search
					or a.StatusID='51' and b.Status like :search
					order by a.Created_at desc;";
				$stmt = $this->db->prepare($sqlcountrow);
				$stmt->bindValue(':search', $search, PDO::PARAM_STR);

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
					$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Tags,a.Viewer,a.Username as 'User',
							a.Updated_at,a.Updated_by,a.StatusID,b.`Status`
						from data_page a
						inner join core_status b on a.StatusID=b.StatusID
						where a.StatusID='51' and a.PageID like :search
						or a.StatusID='51' and a.Title like :search
						or a.StatusID='51' and a.Tags like :search
						or a.StatusID='51' and a.Username like :search
						or a.StatusID='51' and b.Status like :search
						order by a.Created_at desc LIMIT :limpage , :offpage;";
					$stmt2 = $this->db->prepare($sql);
					$stmt2->bindValue(':search', $search, PDO::PARAM_STR);
					$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
					$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						
					if ($stmt2->execute()){
						if ($stmt2->rowCount() > 0){
                            $datares = "[";
					        while($redata = $stmt2->fetch()) {
        					    //Start Tags
								$return_arr = null;
								$names = $redata['Tags'];	
								$named = preg_split( "/[,]/", $names );
								foreach($named as $name){
									if ($name != null){$return_arr[] = utf8_encode(trim($name));}
								}
                                //End Tags
                                
                                $datares .= '{"PageID":'.JSON::safeEncode($redata['PageID']).',
									"Title":'.JSON::safeEncode($redata['Title']).',
									"Image":'.JSON::safeEncode($redata['Image']).',
									"Description":'.JSON::safeEncode($redata['Description']).',
                                    "Tags_Inline":'.JSON::safeEncode($redata['Tags']).',
                                    "Tags":'.JSON::safeEncode($return_arr).',
                                    "Viewer":'.JSON::safeEncode($redata['Viewer']).',
                                    "Created_at":'.JSON::safeEncode($redata['Created_at']).',
                                    "User":'.JSON::safeEncode($redata['User']).',
                                    "Updated_at":'.JSON::safeEncode($redata['Updated_at']).',
                                    "Updated_by":'.JSON::safeEncode($redata['Updated_by']).',
                                    "StatusID":'.JSON::safeEncode($redata['StatusID']).',
                                    "Status":'.JSON::safeEncode($redata['Status']).'},';
                            }
                            $datares = substr($datares, 0, -1);
                            $datares .= "]";
							$pagination = new \classes\Pagination();
							$pagination->lang = $this->lang;
                            $pagination->totalRow = $single['TotalRow'];
                            $pagination->page = $this->page;
                            $pagination->itemsPerPage = $this->itemsPerPage;
                            $pagination->fetchAllAssoc = json_decode($datares);
                            $data = $pagination->toDataArray();
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
		 * Show all data published page paginated public
		 * @return result process in json encoded data
		 */
		public function showPublishPageAsPaginationPublic() {
            if (strtolower($this->sort) != 'asc'){
                $sort = 'desc';
            } else {
                $sort = $this->sort;
            }
			$sqlcountrow = "SELECT count(a.PageID) as TotalRow
					from data_page a
					inner join core_status b on a.StatusID=b.StatusID
					where a.StatusID='51'
					order by a.Created_at $sort;";
				$stmt = $this->db->prepare($sqlcountrow);

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
					$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Tags,a.Viewer,a.Username as 'User',
							a.Updated_at,a.Updated_by,a.StatusID,b.`Status`
						from data_page a
						inner join core_status b on a.StatusID=b.StatusID
						where a.StatusID='51' 
						order by a.Created_at $sort LIMIT :limpage , :offpage;";
					$stmt2 = $this->db->prepare($sql);
					$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
					$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						
					if ($stmt2->execute()){
						if ($stmt2->rowCount() > 0){
                            $datares = "[";
					        while($redata = $stmt2->fetch()) {
        					    //Start Tags
								$return_arr = null;
								$names = $redata['Tags'];	
								$named = preg_split( "/[,]/", $names );
								foreach($named as $name){
									if ($name != null){$return_arr[] = utf8_encode(trim($name));}
								}
                                //End Tags
                                
                                $datares .= '{"PageID":'.JSON::safeEncode($redata['PageID']).',
									"Title":'.JSON::safeEncode($redata['Title']).',
									"Image":'.JSON::safeEncode($redata['Image']).',
									"Description":'.JSON::safeEncode($redata['Description']).',
                                    "Tags_Inline":'.JSON::safeEncode($redata['Tags']).',
                                    "Tags":'.JSON::safeEncode($return_arr).',
                                    "Viewer":'.JSON::safeEncode($redata['Viewer']).',
                                    "Created_at":'.JSON::safeEncode($redata['Created_at']).',
                                    "User":'.JSON::safeEncode($redata['User']).',
                                    "Updated_at":'.JSON::safeEncode($redata['Updated_at']).',
                                    "Updated_by":'.JSON::safeEncode($redata['Updated_by']).',
                                    "StatusID":'.JSON::safeEncode($redata['StatusID']).',
                                    "Status":'.JSON::safeEncode($redata['Status']).'},';
                            }
                            $datares = substr($datares, 0, -1);
                            $datares .= "]";
							$pagination = new \classes\Pagination();
							$pagination->lang = $this->lang;
                            $pagination->totalRow = $single['TotalRow'];
                            $pagination->page = $this->page;
                            $pagination->itemsPerPage = $this->itemsPerPage;
                            $pagination->fetchAllAssoc = json_decode($datares);
                            $data = $pagination->toDataArray();
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
		 * Show all data page written by paginated
		 * @return result process in json encoded data
		 */
		public function showPageWrittenByAsPagination() {
			if (Auth::validToken($this->db,$this->token,$this->username)){
				if (strtolower($this->sort) != 'asc'){
					$sort = 'desc';
				} else {
					$sort = $this->sort;
				}
				$newuser = strtolower(filter_var($this->user,FILTER_SANITIZE_STRING));
				$search = "%$this->search%";
				$sqlcountrow = "SELECT count(a.PageID) as TotalRow
					from data_page a
					inner join core_status b on a.StatusID=b.StatusID
					where b.StatusID = '51' and a.Username = :user and a.Title like :search
					or b.StatusID = '51' and a.Username = :user and a.Tags like :search
					order by a.Created_at $sort;";
				$stmt = $this->db->prepare($sqlcountrow);
				$stmt->bindValue(':search', $search, PDO::PARAM_STR);
				$stmt->bindValue(':user', $newuser, PDO::PARAM_STR);

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
						$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Tags,a.Viewer,a.Username,
								a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
							from data_page a
							inner join core_status b on a.StatusID=b.StatusID
							where b.StatusID = '51' and a.Username = :user and a.Title like :search
							or b.StatusID = '51' and a.Username = :user and a.Tags like :search
							order by a.Created_at $sort LIMIT :limpage , :offpage;";
						$stmt2 = $this->db->prepare($sql);
						$stmt2->bindValue(':search', $search, PDO::PARAM_STR);
						$stmt2->bindValue(':user', $newuser, PDO::PARAM_STR);
						$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
						$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
							
						
						if ($stmt2->execute()){
							if ($stmt2->rowCount() > 0){
								$results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
								$pagination = new \classes\Pagination();
								$pagination->lang = $this->lang;
								$pagination->totalRow = $single['TotalRow'];
								$pagination->page = $this->page;
								$pagination->itemsPerPage = $this->itemsPerPage;
								$pagination->fetchAllAssoc = $results;
								$data = $pagination->toDataArray();
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
		 * Show all data page written by paginated public
		 * @return result process in json encoded data
		 */
		public function showPageWrittenByAsPaginationPublic() {
			if (strtolower($this->sort) != 'asc'){
                $sort = 'desc';
            } else {
                $sort = $this->sort;
            }
			$newuser = strtolower(filter_var($this->user,FILTER_SANITIZE_STRING));
			$search = "%$this->search%";
			$sqlcountrow = "SELECT count(a.PageID) as TotalRow
				from data_page a
				inner join core_status b on a.StatusID=b.StatusID
				where b.StatusID = '51' and a.Username = :user and a.Title like :search
				or b.StatusID = '51' and a.Username = :user and a.Tags like :search
				order by a.Created_at $sort;";
			$stmt = $this->db->prepare($sqlcountrow);
			$stmt->bindValue(':search', $search, PDO::PARAM_STR);
			$stmt->bindValue(':user', $newuser, PDO::PARAM_STR);

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
					$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Tags,a.Viewer,a.Username,
							a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
						from data_page a
						inner join core_status b on a.StatusID=b.StatusID
						where b.StatusID = '51' and a.Username = :user and a.Title like :search
						or b.StatusID = '51' and a.Username = :user and a.Tags like :search
						order by a.Created_at $sort LIMIT :limpage , :offpage;";
					$stmt2 = $this->db->prepare($sql);
					$stmt2->bindValue(':search', $search, PDO::PARAM_STR);
					$stmt2->bindValue(':user', $newuser, PDO::PARAM_STR);
					$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
					$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
							
						
					if ($stmt2->execute()){
						if ($stmt2->rowCount() > 0){
							$results = $stmt2->fetchAll(PDO::FETCH_ASSOC);
							$pagination = new \classes\Pagination();
							$pagination->lang = $this->lang;
							$pagination->totalRow = $single['TotalRow'];
							$pagination->page = $this->page;
							$pagination->itemsPerPage = $this->itemsPerPage;
							$pagination->fetchAllAssoc = $results;
							$data = $pagination->toDataArray();
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
		

		//TAXONOMY=======================================


		private function getAllTrendingPage(){
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			// Query Data
			$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Tags,a.Viewer,a.Username,
					a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
				from data_page a
				inner join core_status b on a.StatusID=b.StatusID
				where b.StatusID = '51'
				order by a.Viewer DESC LIMIT :limpage;";
				
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':limpage', (INT) $newlimit, PDO::PARAM_INT);

			if ($stmt->execute()) {	
    		    if ($stmt->rowCount() > 0){
        	   		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$data = [
			            'results' => $results, 
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
			return $data;
		}

		private function getSeasonalTrendingPage(){
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			// Query Data
			$sql = "SELECT a.PageID,a.Created_at,a.Title,a.Image,a.Description,a.Tags,a.Viewer,a.Username,
					a.Updated_at,a.Updated_by,a.Last_updated,a.StatusID,b.`Status`
				from data_page a
				inner join core_status b on a.StatusID=b.StatusID
				where b.StatusID = '51' AND YEAR(a.Created_at)=YEAR(now()) AND MONTH(a.Created_at) BETWEEN (3*FLOOR((MONTH(now()) % 12)/3)+1) AND MONTH(now())
				order by a.Viewer DESC LIMIT :limpage;";
				
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':limpage', (INT) $newlimit, PDO::PARAM_INT);

			if ($stmt->execute()) {	
    		    if ($stmt->rowCount() > 0){
        	   		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$data = [
			            'results' => $results, 
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
			return $data;
		}

		private function getAllTrendingTags(){
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			$sql = "SELECT UPPER(substr(sub.val,1,1)) as Alpha,sub.val AS Tags, COUNT(*) AS Total
				FROM
				(
					SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.Tags, ',', n.n), ',', -1)) AS val
					FROM (SELECT Substring(data_page.Tags, 1, LENGTH(data_page.Tags) - 0) AS Tags FROM data_page WHERE data_page.StatusID='51') AS t 
					CROSS JOIN 
					(
						   SELECT a.N + b.N * 10 + 1 n
						FROM 
						(SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
						   ,(SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
					) n
					WHERE n.n <= 1 + (LENGTH(t.Tags) - LENGTH(REPLACE(t.Tags, ',', '')))
				) sub
				WHERE val <> ''
				GROUP BY sub.val
				ORDER BY Total DESC,sub.val ASC
				LIMIT :limpage;";
				
			$stmt = $this->db->prepare($sql);		
			$stmt->bindValue(':limpage', (INT) $newlimit, PDO::PARAM_INT);

			if ($stmt->execute()) {	
    		    if ($stmt->rowCount() > 0){
        	   		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$data = [
			            'results' => $results, 
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
			return $data;
		}

		private function getSeasonalTrendingTags(){
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			$sql = "SELECT UPPER(substr(sub.val,1,1)) as Alpha,sub.val AS Tags, COUNT(*) AS Total
				FROM
				(
					SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.Tags, ',', n.n), ',', -1)) AS val
					FROM (SELECT Substring(data_page.Tags, 1, LENGTH(data_page.Tags) - 0) AS Tags FROM data_page WHERE data_page.StatusID='51' AND YEAR(data_page.Created_at)=YEAR(now()) AND MONTH(data_page.Created_at) BETWEEN (3*FLOOR((MONTH(now()) % 12)/3)+1) AND MONTH(now())) AS t 
					CROSS JOIN 
					(
						   SELECT a.N + b.N * 10 + 1 n
						FROM 
						(SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
						   ,(SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
					) n
					WHERE n.n <= 1 + (LENGTH(t.Tags) - LENGTH(REPLACE(t.Tags, ',', '')))
				) sub
				WHERE val <> ''
				GROUP BY sub.val
				ORDER BY Total DESC,sub.val ASC
				LIMIT :limpage;";
				
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':limpage', (INT) $newlimit, PDO::PARAM_INT);

			if ($stmt->execute()) {	
    		    if ($stmt->rowCount() > 0){
        	   		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$data = [
			            'results' => $results, 
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
			return $data;
		}

		/** 
		 * Get all data Trending Page
		 * Note:
		 * - This will get data trending tags from all published pages
		 * - This is cached for 3600 seconds.
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showAllTrendingPage() {
			if (Auth::validToken($this->db,$this->token)){
				$newlimit = Validation::integerOnly($this->limit);
				if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
				if (UniversalCache::isCached('all-trending-page-'.$newlimit,3600)){
                    $datajson = JSON::decode(UniversalCache::loadCache('all-trending-page-'.$newlimit));
                    $data = JSON::decode($datajson->value);
                } else {
					$data = $this->getAllTrendingPage();
                    UniversalCache::writeCache('all-trending-page-'.$newlimit,JSON::encode($data,true),3600);
                }
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Trending Page seasonal
		 * Note:
		 * - This will get data trending page from current season
		 * - This is cached for every 300 seconds
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showSeasonalTrendingPage() {
			if (Auth::validToken($this->db,$this->token)){
				$newlimit = Validation::integerOnly($this->limit);
				if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
				if (UniversalCache::isCached('seasonal-trending-page-'.$newlimit,300)){
                    $datajson = JSON::decode(UniversalCache::loadCache('seasonal-trending-page-'.$newlimit));
                    $data = JSON::decode($datajson->value);
                } else {
					$data = $this->getSeasonalTrendingPage();
                    UniversalCache::writeCache('seasonal-trending-page-'.$newlimit,JSON::encode($data,true),300);
                }
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Trending Page
		 * Note:
		 * - This will get data trending tags from all published pages
		 * - This is cached for 3600 seconds.
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showAllTrendingPagePublic() {
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			if (UniversalCache::isCached('all-trending-page-'.$newlimit,3600)){
                $datajson = JSON::decode(UniversalCache::loadCache('all-trending-page-'.$newlimit));
                $data = JSON::decode($datajson->value);
            } else {
				$data = $this->getAllTrendingPage();
                UniversalCache::writeCache('all-trending-page-'.$newlimit,JSON::encode($data,true),3600);
            }
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Trending Page seasonal
		 * Note:
		 * - This will get data trending page from current season
		 * - This is cached for every 300 seconds
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showSeasonalTrendingPagePublic() {
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			if (UniversalCache::isCached('seasonal-trending-page-'.$newlimit,300)){
                $datajson = JSON::decode(UniversalCache::loadCache('seasonal-trending-page-'.$newlimit));
                $data = JSON::decode($datajson->value);
            } else {
				$data = $this->getSeasonalTrendingPage();
                UniversalCache::writeCache('seasonal-trending-page-'.$newlimit,JSON::encode($data,true),300);
            }
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Tags
		 * Note:
		 * - This will get data trending tags from all published pages
		 * - Slower if you have more 100K of data pages, so you better to cache this at least for 3600 seconds. Default is 3600 seconds.
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showAllTrendingTags() {
			if (Auth::validToken($this->db,$this->token)){
				$newlimit = Validation::integerOnly($this->limit);
				if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
				if (UniversalCache::isCached('all-trending-tags-'.$newlimit,3600)){
                    $datajson = JSON::decode(UniversalCache::loadCache('all-trending-tags-'.$newlimit));
                    $data = JSON::decode($datajson->value);
                } else {
					$data = $this->getAllTrendingTags();
                    UniversalCache::writeCache('all-trending-tags-'.$newlimit,JSON::encode($data,true),3600);
                }
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Trending Tags seasonal
		 * Note:
		 * - This will get data tags from current season
		 * - This is cached for every 300 seconds
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showSeasonalTrendingTags() {
			if (Auth::validToken($this->db,$this->token)){
				$newlimit = Validation::integerOnly($this->limit);
				if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
				if (UniversalCache::isCached('seasonal-trending-tags-'.$newlimit,300)){
                    $datajson = JSON::decode(UniversalCache::loadCache('seasonal-trending-tags-'.$newlimit));
                    $data = JSON::decode($datajson->value);
                } else {
					$data = $this->getSeasonalTrendingTags();
                    UniversalCache::writeCache('seasonal-trending-tags-'.$newlimit,JSON::encode($data,true),300);
                }
			} else {
				$data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
			}		
        
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Tags (public)
		 * Note:
		 * - This will get data trending tags from all published pages
		 * - Slower if you have more 100K of data pages, so you better to cache this at least for 3600 seconds. Default is 3600 seconds.
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showAllTrendingTagsPublic() {
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			if (UniversalCache::isCached('all-trending-tags-'.$newlimit,3600)){
                $datajson = JSON::decode(UniversalCache::loadCache('all-trending-tags-'.$newlimit));
                $data = JSON::decode($datajson->value);
            } else {
				$data = $this->getAllTrendingTags();
                UniversalCache::writeCache('all-trending-tags-'.$newlimit,JSON::encode($data,true),3600);
            }
			return JSON::encode($data,true);
	        $this->db= null;
		}

		/** 
		 * Get all data Trending Tags seasonal (public)
		 * Note:
		 * - This will get data tags from current season
		 * - This is cached for every 300 seconds
		 * - Default is limited to 100 rows
		 * @return result process in json encoded data
		 */
		public function showSeasonalTrendingTagsPublic() {
			$newlimit = Validation::integerOnly($this->limit);
			if (empty($newlimit) || $newlimit < 1 || $newlimit > 1000) $newlimit = 100;
			if (UniversalCache::isCached('seasonal-trending-tags-'.$newlimit,300)){
				$datajson = JSON::decode(UniversalCache::loadCache('seasonal-trending-tags-'.$newlimit));
				$data = JSON::decode($datajson->value);
            } else {
				$data = $this->getSeasonalTrendingTags();
                UniversalCache::writeCache('seasonal-trending-tags-'.$newlimit,JSON::encode($data,true),300);
            }
			return JSON::encode($data,true);
	        $this->db= null;
		}

        
        //STATUS=======================================


		/** 
		 * Get all data Status for Release
		 * @return result process in json encoded data
		 */
		public function showOptionRelease() {
			if (Auth::validToken($this->db,$this->token)){
				$sql = "SELECT a.StatusID,a.Status
					FROM core_status a
					WHERE a.StatusID = '51' OR a.StatusID = '52'
					ORDER BY a.Status ASC";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
        	   		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'results' => $results, 
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
        
			return JSON::encode($data,true);
	        $this->db= null;
		}


		//SUMMARY=======================================


		/** 
		 * Get data statistic page
		 * @return result process in json encoded data
		 */
		public function statPageSummary() {
			if (Auth::validToken($this->db,$this->token)){
				$newusername = strtolower($this->username);
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1' || $roles == '2'){
					$sql = "SELECT 
						(SELECT count(x.PageID) FROM data_page x WHERE x.StatusID='51') AS 'Publish',
						(SELECT count(x.PageID) FROM data_page x WHERE x.StatusID='52') AS 'Draft',
						(SELECT sum(x.Viewer) FROM data_page x) AS 'Viewer',
						(SELECT count(x.PageID) FROM data_page x) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Draft))/(SELECT Total))*100),0) AS 'Percent_Up',
						IFNULL((100 - (SELECT Percent_Up)),0) AS 'Percent_Down';";
					$stmt = $this->db->prepare($sql);
				} else {
					$sql = "SELECT 
						(SELECT count(x.PageID) FROM data_page x WHERE x.StatusID='51' AND x.Username=:username) AS 'Publish',
						(SELECT count(x.PageID) FROM data_page x WHERE x.StatusID='52' AND x.Username=:username) AS 'Draft',
						(SELECT sum(x.Viewer) FROM data_page x WHERE x.Username=:username) AS 'Viewer',
						(SELECT count(x.PageID) FROM data_page x WHERE x.Username=:username) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Draft))/(SELECT Total))*100),0) AS 'Percent_Up',
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
		 * Get data statistic page in Year
		 * @return result process in json encoded data
		 */
        public function statPageYear(){
			if (Auth::validToken($this->db,$this->token)){
				$newyear = Validation::integerOnly($this->year);
				$newusername = strtolower($this->username);
				$roles = Auth::getRoleID($this->db,$this->token);
				if($roles == '1' || $roles == '2'){
					$sql = "SELECT 
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 1 GROUP BY MONTH(a.Created_at)) AS 'Jan',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 2 GROUP BY MONTH(a.Created_at)) AS 'Feb',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 3 GROUP BY MONTH(a.Created_at)) AS 'Mar',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 4 GROUP BY MONTH(a.Created_at)) AS 'Apr',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 5 GROUP BY MONTH(a.Created_at)) AS 'May',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 6 GROUP BY MONTH(a.Created_at)) AS 'Jun',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 7 GROUP BY MONTH(a.Created_at)) AS 'Jul',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 8 GROUP BY MONTH(a.Created_at)) AS 'Aug',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 9 GROUP BY MONTH(a.Created_at)) AS 'Sep',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 10 GROUP BY MONTH(a.Created_at)) AS 'Oct',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 11 GROUP BY MONTH(a.Created_at)) AS 'Nov',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 12 GROUP BY MONTH(a.Created_at)) AS 'Dec';";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':newyear', $newyear, PDO::PARAM_STR);
				} else {
					$sql = "SELECT 
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 1 GROUP BY MONTH(a.Created_at)) AS 'Jan',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 2 GROUP BY MONTH(a.Created_at)) AS 'Feb',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 3 GROUP BY MONTH(a.Created_at)) AS 'Mar',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 4 GROUP BY MONTH(a.Created_at)) AS 'Apr',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 5 GROUP BY MONTH(a.Created_at)) AS 'May',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 6 GROUP BY MONTH(a.Created_at)) AS 'Jun',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 7 GROUP BY MONTH(a.Created_at)) AS 'Jul',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 8 GROUP BY MONTH(a.Created_at)) AS 'Aug',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 9 GROUP BY MONTH(a.Created_at)) AS 'Sep',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 10 GROUP BY MONTH(a.Created_at)) AS 'Oct',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 11 GROUP BY MONTH(a.Created_at)) AS 'Nov',
						(SELECT count(a.PageID) AS Total FROM data_page a WHERE a.Username=:username AND YEAR(a.Created_at) = :newyear AND MONTH(a.Created_at) = 12 GROUP BY MONTH(a.Created_at)) AS 'Dec';";
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