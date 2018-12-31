<?php

namespace modules\genuid;                           //Make sure namespace is same structure with parent directory

use \classes\Auth as Auth;                          //For authentication internal user
use \classes\CustomHandlers as CustomHandlers;      //To get default response message
use \classes\JSON as JSON;                          //For handling JSON in better way
use \modules\genuid\UUID as UUID;                   //UUID class
use PDO;                                            //To connect with database

	/**
     * Genuid class
     *
     * @package    modules/genuid
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2019 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim-modules-genuid/blob/master/LICENSE.md  MIT License
     */
    class Genuid {

        // database var
        protected $db;

        //base var
        protected $basepath,$baseurl,$basemod;

        //master var
        var $username,$token;
        
        //multi language
        var $lang;

        //data var
        var $namespace,$name,$prefix='',$suffix='',$abs=true,$lenght=13;
        
        //construct database object
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

        /**
         * Numeric randomizer to make more unique
         * Note: This randomizer is act like salt.
         * 
         * @return string with 10 digits
         */
        public function numeric_randomizer(){
            $data = mt_rand();
            $pad = (10 - strlen($data));
            if($pad > 0){
                $leading = "";
                for ($i=1;$i<=$pad;$i++){
                    $leading .= '0';
                }
                return str_replace('-','00',str_pad($data, 10, $leading, STR_PAD_LEFT));
            }
            return str_replace('-','0',$data);
        }


        /**
         * Generate UUID V3
         * Note: V3 is use MD5
         * 
         * @property namespace is the uuid base
         * @property name is the data source
         * 
         * @return string uuid version 3
         */
        public function generate_uuidV3() {
            return UUID::v3($this->namespace,$this->name);
        }

        /**
         * Generate UUID V4
         * 
         * @return string uuid version 4
         */
        public function generate_uuidV4() {
            return UUID::v4();
        }

        /**
         * Generate UUID V5
         * Note: V5 is use SHA1
         * 
         * @property namespace is the uuid base
         * @property name is the data source
         * 
         * @return string uuid version 5
         */
        public function generate_uuidV5() {
            return UUID::v5($this->namespace,$this->name);
        }

        /**
         * Generate Short ID very fast with uniqid + crc32 + dechex + randomizer
         * 
         * @return string alphanumeric with 8 chars
         */
        public function generate_short_dechex(){
            $data = dechex(crc32(uniqid($this->numeric_randomizer(),true)));
            $pad = (8 - strlen($data));
            if($pad > 0){
                $leading = "";
                for ($i=1;$i<=$pad;$i++){
                    $leading .= '0';
                }
                return $this->prefix.str_replace('-','00',str_pad($data, 8, $leading, STR_PAD_LEFT)).$this->suffix;
            }
            return $this->prefix.str_replace('-','0',$data).$this->suffix;
        }

        /**
         * Generate Short ID very fast with uniqid + crc32 + base_convert + randomizer
         * 
         * @return string alphanumeric with 7 chars
         */
        public function generate_short_base(){
            $data = base_convert(crc32(uniqid($this->numeric_randomizer(),true)), 10, 36);
            $pad = (7 - strlen($data));
            if($pad > 0){
                $leading = "";
                for ($i=1;$i<=$pad;$i++){
                    $leading .= '0';
                }
                return $this->prefix.str_replace('-','00',str_pad($data, 7, $leading, STR_PAD_LEFT)).$this->suffix;
            }
            return $this->prefix.str_replace('-','0',$data).$this->suffix;
        }

        /**
         * Generate Unique ID very fast with uniqid + more_entropy + randomizer
         * 
         * @return string alphanumeric with 32 chars
         */
        public function generate_uniqid_long(){
            return $this->prefix.str_replace('.','',uniqid($this->numeric_randomizer(),true)).$this->suffix;
        }

        /**
         * Generate Unique ID very fast with uniqid + randomizer
         * 
         * @return string alphanumeric with 23 chars
         */
        public function generate_uniqid_simple(){
            return $this->prefix.uniqid($this->numeric_randomizer()).$this->suffix;
        }

        /**
         * Generate Short Numeric ID very fast with uniqid + crc32 + randomizer
         * 
         * @return string numeric with 10 digits
         */
        public function generate_uniqid_numeric(){
            $salt = $this->numeric_randomizer();
            $data = (($this->abs)?abs(crc32(uniqid($salt))):crc32(uniqid($salt)));
            $pad = (10 - strlen($data));
            if($pad > 0){
                $leading = "";
                for ($i=1;$i<=$pad;$i++){
                    $leading .= '0';
                }
                return $this->prefix.str_replace('-','00',str_pad($data, 10, $leading, STR_PAD_LEFT)).$this->suffix;
            }
            return $this->prefix.str_replace('-','0',$data).$this->suffix;
        }

        /**
         * Generate Unique ID very fast with random or pseudo bytes
         * 
         * @property lenght you can adjust the digits to below 13 length, but carefull about the uniqueness, default will return 13 chars.
         * 
         * @return string alphanumeric with 13 chars
         */
        public function generate_unique_custom() {
            if (function_exists("random_bytes")) {
                $bytes = random_bytes(ceil($this->lenght / 2));
            } elseif (function_exists("openssl_random_pseudo_bytes")) {
                $bytes = openssl_random_pseudo_bytes(ceil($this->lenght / 2));
            } else {
                throw new Exception("no cryptographically secure random function available");
            }
            return $this->prefix.substr(bin2hex($bytes), 0, $this->lenght).$this->suffix;
        }

        public function uuidv3(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $uuid = $this->generate_uuidV3();
                if(!empty($uuid)){
                    $data = [
                        'result' => [
                            'type' => 'uuid',
                            'version' => 3,
                            'base' => 36,
                            'namespace' => $this->namespace,
                            'name' => $this->name,
                            'id' => $uuid
                        ],
                        'status' => 'success',
    					'code' => 'RS101',
            	    	'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang)    
                    ];
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
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function uuidv4(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $uuid = $this->generate_uuidV4();
                if(!empty($uuid)){
                    $data = [
                        'result' => [
                            'type' => 'uuid',
                            'version' => 4,
                            'base' => 36,
                            'id' => $uuid
                        ],
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
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function uuidv5(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $uuid = $this->generate_uuidV5();
                if(!empty($uuid)){
                    $data = [
                        'result' => [
                            'type' => 'uuid',
                            'version' => 5,
                            'base' => 36,
                            'namespace' => $this->namespace,
                            'name' => $this->name,
                            'id' => $uuid
                        ],
                        'status' => 'success',
    					'code' => 'RS101',
            	    	'message' => CustomHandlers::getreSlimMessage('RS101',$this->lang)    
                    ];
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
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function short_dechex(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $id = $this->generate_short_dechex();
                if(!empty($id)){
                    $data = [
                        'result' => [
                            'type' => 'dechex',
                            'base' => 8,
                            'prefix' => $this->prefix,
                            'suffix' => $this->suffix,
                            'id' => $id
                        ],
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
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function short_base(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $id = $this->generate_short_base();
                if(!empty($id)){
                    $data = [
                        'result' => [
                            'type' => 'baseconvert',
                            'base' => 7,
                            'prefix' => $this->prefix,
                            'suffix' => $this->suffix,
                            'id' => $id
                        ],
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
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function uniqid_long(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $id = $this->generate_uniqid_long();
                if(!empty($id)){
                    $data = [
                        'result' => [
                            'type' => 'uniqid_long',
                            'base' => 32,
                            'prefix' => $this->prefix,
                            'suffix' => $this->suffix,
                            'id' => $id
                        ],
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
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function uniqid_simple(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $id = $this->generate_uniqid_simple();
                if(!empty($id)){
                    $data = [
                        'result' => [
                            'type' => 'uniqid_simple',
                            'base' => 23,
                            'prefix' => $this->prefix,
                            'suffix' => $this->suffix,
                            'id' => $id
                        ],
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
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function uniqid_numeric(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $id = $this->generate_uniqid_numeric();
                if(!empty($id)){
                    $data = [
                        'result' => [
                            'type' => 'uniqid_numeric',
                            'base' => 10,
                            'prefix' => $this->prefix,
                            'suffix' => $this->suffix,
                            'id' => $id
                        ],
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
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }

        public function unique_custom(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $id = $this->generate_unique_custom();
                if(!empty($id)){
                    $data = [
                        'result' => [
                            'type' => 'unique_custom',
                            'base' => 13,
                            'prefix' => $this->prefix,
                            'suffix' => $this->suffix,
                            'id' => $id
                        ],
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
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401',$this->lang)
				];
            }
            return JSON::encode($data,true);
        }
    }