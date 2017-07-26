<?php

class Login{

    public function __construct(){

        session_start();
				require_once("../config/db.php");
				
    }
		
		
		public function doLogin(){
			
      if(empty($_POST['loginArray'][0])){return "user";}
			
			elseif(empty($_POST['loginArray'][1])){return "password";}
			
			elseif(!empty($_POST['loginArray'][0]) && !empty($_POST['loginArray'][1])){

          $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
					
          // change character set to utf8 and check it
          if (!$this->db_connection->set_charset("utf8")) {
              $this->errors[] = $this->db_connection->error;
          }

          if (!$this->db_connection->connect_errno) {

              $user_name = $this->db_connection->real_escape_string($_POST['loginArray'][0]);

              $sql = "SELECT * FROM users WHERE user_email ='$user_name';";
              $result_of_login_check = $this->db_connection->query($sql);

              // if this user exists
              if ($result_of_login_check->num_rows==1) {
									
                  // get result row (as an object)
                  $result_row = $result_of_login_check->fetch_object();
									
                  // using PHP 5.5's password_verify() function to check if the provided password fits
                  // the hash of that user's password
                  if (password_verify($_POST['loginArray'][1],$result_row->password_hash)) {

                      // write user data into SESSION
                      $_SESSION['user_id'] = $result_row->id;
											$_SESSION['user_status'] = $result_row->status;
											if(!empty($result_row->user_name)){$_SESSION['user_name']=$result_row->user_name;}
											if(!empty($result_row->user_name)){$_SESSION['user_team']=$result_row->user_team;}
                      $_SESSION['user_login_status'] = 1;
											return "success";

                  }
									else{//email exists but password is wrong
										
										if(empty($result_row->password_hash) && !empty($result_row->invite_code)){
											
											if($_POST['loginArray'][1] == $result_row->invite_code){
												
												$_SESSION['user_id'] = $result_row->id;
												$_SESSION['activate_code'] = $result_row->invite_code;
												
												return "activate";
												
											}else{return "password";}
											
										}
										else{return "password";}
										
                  }
									
              }
							else{return "user";}
							
          }//no connection errors
      
			}//username and password arent empty
				
		}
		
		public function registerPassword(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			
      // change character set to utf8 and check it
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
					
				$pw_data = $this->db_connection->real_escape_string($_POST['passwordData']);				
			
				parse_str($pw_data, $output);

		    if(empty($output['user_password_new']) || empty($output['user_password_repeat'])){
		    	return "shortpass";
		    }elseif ($output['user_password_new'] !== $output['user_password_repeat']){
		    	return "passmatch";
		    } elseif (strlen($output['user_password_new']) < 6) {
		    	return "shortpass";
		    }elseif (!empty($output['user_password_new'])
		    	&& !empty($output['user_password_repeat'])
		    		&& ($output['user_password_new'] === $output['user_password_repeat'])
		    ){
						
					$user_id = $this->db_connection->real_escape_string($_SESSION['user_id']);
					$activate_code = $this->db_connection->real_escape_string($_SESSION['activate_code']);
					
					$user_password_hash = password_hash($output['user_password_new'], PASSWORD_DEFAULT);
					
          // check if user or email address already exists
          $sql = "SELECT * FROM users WHERE id=$user_id AND invite_code=$activate_code;";
          $query_check_user_name = $this->db_connection->query($sql);
					
          if($query_check_user_name->num_rows == 1){
						
            // write new user's data into database
            $sql = "UPDATE users SET password_hash='$user_password_hash',status='1' 
										WHERE id='$user_id' AND invite_code = $activate_code";
            $query_new_user_insert = $this->db_connection->query($sql);

            // if user has been added successfully
            if ($query_new_user_insert) {
							
				      $_SESSION = array();
				      
              $_SESSION['user_id'] = $user_id;
							$_SESSION['user_status'] = 1;
							$_SESSION['user_name']= "";
							$_SESSION['user_team']= "";
              $_SESSION['user_login_status'] = 1;
              
							return "success";
							
            } else {
                return "unknownfail";
            }
						
					}
					
				}
					
			}
			
		}
		
    public function isUserLoggedIn(){
        
				if (isset($_SESSION['user_login_status']) && $_SESSION['user_login_status']==1){
					return true;
				}
        
        return false;
    }
		
		public function doLogout(){
      $_SESSION = array();
      session_destroy();
		}
		
		public function registerTeam(){
			
      $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			
      // change character set to utf8 and check it
      if (!$this->db_connection->set_charset("utf8")) {
          $this->errors[] = $this->db_connection->error;
      }

      if (!$this->db_connection->connect_errno) {
					
				$team_data = $this->db_connection->real_escape_string($_POST['teamData']);				
			
				parse_str($team_data, $output);
				
				$user_id = $_SESSION['user_id'];
				$user_name = $output['user_name'];
				$user_team = $output['user_team'];
				
		    if(empty($user_name) || empty($user_team)){
		    	return "shortpass";
		    } elseif (strlen($user_name) < 2) {
		    	return "shortuser";
		    } elseif (strlen($user_team) < 6) {
		    	return "shortteam";
		    } elseif (strlen($user_name) > 25) {
		    	return "longuser";
		    } elseif (strlen($user_team) > 25) {
		    	return "longteam";
				} elseif (!preg_match('/^[a-z0-9 !\@\#\$\%\^\&\*\-\+\=\?]+$/i', $user_team)){
					return "funnychars";
				} elseif (!preg_match('/^[a-z0-9 ]+$/i', $user_name)){
					return "funnychars";
		    }elseif (!empty($user_name) && !empty($user_team)){
						
					//check that team name isnt taken
          $sql = "SELECT id FROM users WHERE user_team = '$user_team';";
          $query_check_user_team = $this->db_connection->query($sql);

          if($query_check_user_name->num_rows==1){//returned a row with team name
              echo "teamexists";
          }else{
						
            $sql = "UPDATE users SET user_name='$user_name',user_team='$user_team',status='2' 
										WHERE id='$user_id'";
            $query_new_user_insert = $this->db_connection->query($sql);
						
						$_SESSION['user_name'] = $user_name;
						$_SESSION['user_team'] = $user_team;
						
						return "success";
					}
				}
					
			}
			
		}
		
}//class Login
