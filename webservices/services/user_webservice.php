<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * user_webservice.php - User webservice for Stud.IP
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'lib/webservices/api/studip_user.php';


/**
 * Service definition regarding Stud.IP users.
 *
 * @package   studip
 * @package   webservice
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */

class UserService extends AccessControlledService {


   /**
    * This parses an old-style Stud.IP message string and strips off all markup
    *
    * @param string $long_msg Stud.IP messages, concatenated with $separator
    * @param string $separator
    */
   static function parse_msg_to_clean_text($long_msg,$separator="§") {
       $msg = explode ($separator,$long_msg);
       $ret = array();
       for ($i=0; $i < count($msg); $i=$i+2) {
           if ($msg[$i+1]) $ret[] = trim(decodeHTML(preg_replace ("'<[\/\!]*?[^<>]*?>'si", "", $msg[$i+1])));
       }
       return join("\n", $ret);
   }

   function UserService() {
       $this->add_api_method('create_user',
           array('', 'Studip_User'),
           'Studip_User',
           'creates a new user');
       $this->add_api_method('find_user_by_user_name',
           array('', ''),
           'Studip_User',
           'finds a user by username');
       $this->add_api_method('update_user',
           array('', 'Studip_User'),
           'Studip_User',
           'updates user');
       $this->add_api_method('update_password', 
       	    array('', 'Studip_User', ''), 
 		    true, 
 		    'updates a users password'); 
       $this->add_api_method('delete_user',
           array('', ''),
           true,
           'deletes user with given username');
       $this->add_api_method('check_credentials',
           array('', '', ''),
           true,
           'checks if given username and password match');
   }

   /**
    * This method is called before every other service method and tries to
    * authenticate an incoming request using the before_filter() in the parent.
    * Additionaly it sets up a faked Stud.IP environment using globals $auth, $user, $perm,
    * so that the service methods will run with Stud.IPs root permission.
    *
    * @param string the function's name.
    * @param array an array of arguments that will be delivered to the function.
    *
    * @return mixed if this method returns a Studip_Ws_Fault, further
    *               processing will be aborted
    */
   function before_filter($name, &$args) {
       global $auth, $user, $perm;

       $auth = new Seminar_Auth();
       $auth->auth = array('uid' => 'ws',
          'uname' => 'ws',
          'perm' => 'root');
       $faked_root = new User();
       $faked_root->user_id = 'ws';
       $faked_root->username = 'ws';
       $faked_root->perms = 'root';
       $user = new Seminar_User($faked_root);
       $perm = new Seminar_Perm();
       $GLOBALS['MAIL_VALIDATE_BOX'] = false;

       return parent::before_filter($name, $args);

   }


  /**
   * Create a User using a User struct as defined in the service's WSDL.
   *
   * @param string the api key.
   * @param string a partially filled User struct.
   *
   * @return string the updated User struct. At least the user's id is filled
   *                in. If the user cannot be created, a fault containing a
   *                textual representation of the error will be delivered
   *                instead.
   */
  function create_user_action($api_key, $user) {
      
      return UserService::create_studipuser($user);
      
  }


  /**
   * Searches for a user using the user's user name.
   *
   * @param string the api key.
   * @param string the user's username.
   *
   * @return mixed the found User struct or a fault if the user could not be
   *               found.
   */
  function find_user_by_user_name_action($api_key, $user_name) {
      
    $user = Studip_User::find_by_user_name($user_name);
    
    if (!$user)
            return new Studip_Ws_Fault('No such user.');
    
    $entry = KuferMapping::findOneByStudip_id($user->id);
    $user->id = $entry->id;

    return $user;
  }


  /**
   * Updates a user.
   *
   * @param string the api key.
   * @param array an array representation of an user
   *
   * @return mixed the user's User struct or a fault if the user could not be
   *               updated.
   */
  function update_user_action($api_key, $user) {

    $user = new Studip_User($user);
    $kufer_id = $user->id;
    
     if (!$kufer_id)
            return new Studip_Ws_Fault('You have to give the user\'s id.');
    
    $entry = KuferMapping::find($kufer_id);
    $user_id = $entry->studip_id;  
      
    $user->id = $user_id;

    if (!$user->id)
            return new Studip_Ws_Fault('No such user.');

    if (!$user->save())
        return new Studip_Ws_Fault(self::parse_msg_to_clean_text($user->error));

    //$retun_user = Studip_User::find_by_user_id($user->id);
    $user->id = $kufer_id;
    return $user;
  }
 /** 
 	     * Updates an users password. 
 	     * 
 	     * @param string the api key. 
 	     * @param array an array representation of an user 
 	     * @param string $password the new password 
 	     * 
 	     * @return boolean 
 	     */ 
 	    function update_password_action($api_key, $user, $password) { 
	/** 
	    $user = new Studip_User($user); 
            $entry = KuferMapping::find($user->id);
            $user_id = $entry->studip_id;
        
            if(!$user_id){ 
                return new Studip_Ws_Fault('User_id not found!');
            }

            try {
            //$user = Studip_User::getInstance($user_id);
            
                $hasher = new PasswordHash(8, $GLOBALS['PHPASS_USE_PORTABLE_HASH']); 
	 
                $update_user = new User($user_id); 
                $update_user->password = $hasher->HashPassword($password); 
	 
                if (!$update_user->store()) { 
                    return new Studip_Ws_Fault('Could not store new password!'); 
                } 
            }   catch (Exception $ex) {
                return new Studip_Ws_Fault($ex->getMessage());
            }
	 **/
	        return true; 
	    } 

  /**
   * Deletes a user.
   *
   * @param string the api key.
   * @param string the user's username.
   *
   * @return boolean returns TRUE if deletion was successful or a fault
   *                 otherwise.
   */
  function delete_user_action($api_key, $user_name) {

    $user = Studip_User::find_by_user_name($user_name);
    //$user = new User($user_id); 

    if (!$user)
            return new Studip_Ws_Fault('No such user.');

    if (!$user->destroy())
        return new Studip_Ws_Fault(self::parse_msg_to_clean_text($user->error));

    $entry = KuferMapping::findOneByStudip_id($user->id);
    $entry->delete();
    return TRUE;
  }

  /**
   * check authentication for a user.
   *
   * @param string the api key.
   * @param string the user's username.
   * @param string the user's username.
   *
   * @return boolean returns TRUE if authentication was successful or a fault
   *                 otherwise.
   */
  function check_credentials_action($api_key, $username, $password) {
      list($user_id, $error_msg, $is_new_user) = array_values(StudipAuthAbstract::CheckAuthentication($username, $password));
      if($user_id === false){
          return new Studip_Ws_Fault(strip_tags($error_msg));
      } else {
          return true;
      }
  }
  
  
  function sonderzeichen($string)
    {
     $string = str_replace("ä", "ae", $string);
     $string = str_replace("ü", "ue", $string);
     $string = str_replace("ö", "oe", $string);
     $string = str_replace("Ä", "Ae", $string);
     $string = str_replace("Ü", "Ue", $string);
     $string = str_replace("Ö", "Oe", $string);
     $string = str_replace("ß", "ss", $string);
     $string = str_replace("´", "", $string);
     return $string;
    }
    
    function create_studipuser($user){
      $user = new Studip_User($user);
      
      //falls es schon einen Utzer mit dieser Mail-Adresse gibt (autor, tutor, dozent), wird dieser in der Mapping Tabelle verknüpft und zurückgegeben
      $studip_user = Studip_User::findOneByMail($user->email);
      if ($studip_user){
           $entry = KuferMapping::findOneByStudip_id($studip_user->id);
           if(!$entry){ //falls es für diesen Nutzer wirklich noch keinen Eintrag gab, Create entry and exchange user id
                $entry = new KuferMapping();
                $entry->studip_id = $studip_user->id;
                $entry->claimed = time();
                $entry->kufer_username = $user->user_name;
                $entry->mkdate = time();
                $entry->store();
           }
           //für die Rückgabe die Mapping-ID verwenden
           $studip_user->id = $entry->ID;  
           return $studip_user;
      } else {
          //falls der Nutzer komplett neu ist, temporären Nutzer anlegen
          //speichern, Eintrag in Kufer-Mapping Tabelle und Rückgabe des Users mit der gemappten ID
          //$email; $permission; $fullname; übernehmen, den Rest anonymisieren
          if (!$user->last_name)
              return false;
          
          $i = 1;
          $user_name = substr($this->sonderzeichen($user->first_name), 0, $i) . $this->sonderzeichen($user->last_name);
          while (Studip_User::find_by_user_name($user_name) && $i<= strlen($this->sonderzeichen($user->first_name))){
              $i++;
              $user_name = substr($this->sonderzeichen($user->first_name), 0, $i) . $this->sonderzeichen($user->last_name);
          }

          $md5_user = new User();
          $md5_user->username = $user_name;
          $md5_user->vorname = '';
          $md5_user->nachname = 'Vorläufiger Nutzer';
          $md5_user->email = $user->email;
          $md5_user->perms = $user->permission;
          $md5_user->auth_plugin = 'standard';
          
           if (!$md5_user->store())
              return new Studip_Ws_Fault(self::parse_msg_to_clean_text($user->user_name));

          //Create entry and exchange user id
          $entry = new KuferMapping();
          $entry->studip_id = $md5_user->user_id;
          //unbestätigter nutzer: confirmed = false
          $entry->claimed = NULL;
          $entry->mkdate = time();
          $entry->store();
          $user->id = $entry->ID;  

          return $user;
      }
    }
    
}
