<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * seminar_webservice.php - Provides webservices for infos about
 *  Seminars
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/webservices/api/studip_seminar.php');

class SeminarService extends AccessControlledService
{
    function SeminarService()
    {
    $this->add_api_method('get_participants',
                          array('string', 'string'),
                          array('string'),
                          'gets participants for seminar');
    $this->add_api_method('get_users_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all users for seminar');
    $this->add_api_method('get_authors_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all authors for seminar');
    $this->add_api_method('get_tutors_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all tutors for seminar');
    $this->add_api_method('get_lecturers_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all lecturers for seminar');
    $this->add_api_method('get_admins_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all admins for seminar');
    $this->add_api_method('get_seminar_groups',
                          array('string', 'string'),
                          array('string'),
                          'gets all groups for seminar');
    $this->add_api_method('get_seminar_group_members',
                          array('string', 'string', 'string'),
                          array('string'),
                                                    'gets all group members for seminar');

    $this->add_api_method('validate_seminar_permission',
                          array('string', 'string', 'string', 'string'),
                          array('string'),
                                                    'validates permissions in seminar');

  #  $this->add_api_method('validate_institute_permission',
  #                        array('string', 'string', 'string', 'string'),
  #                        array('string'),
    #                                               'validates permissions in institute');
    
     $this->add_api_method('create_course', 
	                        array('string', 'Studip_Seminar_Info'), 
	                        'string', 
	                        'creates a new course'); 
	         
	    $this->add_api_method('update_course', 
	                         array('string', 'string', 'Studip_Seminar_Info'), 
	                         'null', 
	                         'updates an existing course'); 
	         
	    $this->add_api_method('delete_course', 
	                         array('string', 'string'), 
	                         'null', 
	                         'deletes an existing course'); 
	         
	    $this->add_api_method('insert_user_into_course', 
	                          array('string', 'string', 'Studip_User', 'string'), 
	                          'null', 
	                          'inserts a user into the course'); 
	         
	    $this->add_api_method('remove_user_from_course', 
	                          array('string', 'string', 'Studip_User'), 
	                          'null', 
	                          'removes a user from the course'); 
	
    }

    function validate_seminar_permission_action($api_key, $ticket, $seminar_id, $permission)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        $seminar = new StudipSeminarHelper();
        return $seminar->validate_seminar_permission($ticket, $seminar_id, $permission);
    }

    function get_participants_action($api_key, $seminar_id)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        $seminar = new StudipSeminarHelper();
        $participants = $seminar->get_participants($seminar_id);
        
        foreach ($participants as $key => $value){
            $entry = KuferMapping::findOneByStudip_id($value);
            $participants[$key] = $entry->id;
        }
                
        return $participants;
    }

    function get_users_for_seminar_action($api_key, $seminar_id)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        return StudipSeminarHelper::get_participants($seminar_id, 'user');
    }

    function get_authors_for_seminar_action($api_key, $seminar_id)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        return StudipSeminarHelper::get_participants($seminar_id, 'autor');
    }

    function get_tutors_for_seminar_action($api_key, $seminar_id)
    {
        $entry = KuferMapping::findOneByID($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        return StudipSeminarHelper::get_participants($seminar_id, 'tutor');
    }

    function get_lecturers_for_seminar_action($api_key, $seminar_id)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        $lecturers = StudipSeminarHelper::get_participants($seminar_id, 'dozent');
        return $lecturers;
    }

    function get_admins_for_seminar_action($api_key, $seminar_id)
    {
        $entry = KuferMapping::findOneByID($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        $authorized_users = StudipSeminarHelper::get_admins_for_seminar($seminar_id);
        return $authorized_users;
    }

    function get_seminar_groups_action($api_key, $seminar_id)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        return StudipSeminarHelper::get_seminar_groups($seminar_id);
    }

    function get_seminar_group_members_action($api_key, $seminar_id, $group_name)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }
        return StudipSeminarHelper::get_seminar_group_members($seminar_id, $group_name);
    }
    
    function before_filter($name, &$args)
    {
        global $perm;

        $perm = new Seminar_Perm();

        return parent::before_filter($name, $args);
    }

    function create_course_action($api_key, $seminar_info)
    {
        $semester = SemesterData::getCurrentSemesterData();

        try {
            $sem = Seminar::getInstance();
            $sem->name = $seminar_info['title'];
            $sem->seminar_number = $seminar_info['lecture_number'];
            $sem->institut_id = $seminar_info['institute_id'];
            $sem->status = 1;
            $sem->form = 1;
            $sem->read_level = 1;
            $sem->write_level = 1;
            $sem->admission_prelim = 0;
            $sem->visible = 1;
            $sem->semester_start_time = $semester['beginn'];
            $sem->semester_duration_time = 0;
            $sem->store(false);
            $sem->setInstitutes(array($sem->institut_id));

            if(isset($seminar_info['lecturers'])){
                foreach ($seminar_info['lecturers'] as $user) {
                    $entry = KuferMapping::find($user['id']);
                    $user_id = $entry->studip_id;
                    $sem->addMember($user_id, 'dozent', true);
                }
            }
            
            if(isset($seminar_info['description'])){
                $sem->description = $seminar_info['description'];
            }
            
            create_folder(_('Allgemeiner Dateiordner'),
                      _('Ablage für allgemeine Ordner und Dokumente der Veranstaltung'),
                      $sem->getId(),
                      7,
                      $sem->getId());
            
            if(isset($seminar_info['dates'])){
                foreach ($seminar_info['dates'] as $date) {
                    //$entry = KuferMapping::find($user['id']);
                    //$date_id = $entry->studip_id;
                    $termin = new SingleDate(array('seminar_id' => $sem->getId()));

                    //freie Raumangabe location
                    $termin->setFreeRoomText($date['location']);

                    $termin->setTime($date['begin_time'], $date['end_time']);
                    $termin->setDateType('1');

                    //zugeordnete Dozenten eintragen
                    if(isset($date['lecturers'])){
                        foreach ($date['lecturers'] as $user) {
                            $entry = KuferMapping::find($user['id']);
                            $user_id = $entry->studip_id;
                            $termin->relatedPerson($user_id);
                        }
                    }

                    if(isset($date['description'])){
                        //find Topic
                        $query = "SELECT issue_id FROM themen WHERE title LIKE :beschreibung AND seminar_id LIKE :sem_id";
                        $statement = DBManager::get()->prepare($query);
                        $statement->execute(array(':beschreibung' => $date['description'], ':sem_id' => $sem->getId()));
                        $topicID = $statement->fetch()[0];

                        //else create topic (Thema) an add
                        if (!$topicID){
                            $issue = new Issue(array('seminar_id' => $sem->getId()));
                            $issue->setTitle($date['description']);
                            $issue->setDescription($date['description']);
                            $issue->store();
                            $topicID = $issue->getIssueID();
                        } 
                        $termin->addIssueID($topicID);

                    }

                    $termin->store();
                    
                    $entry = new KuferDateMapping($date['id']);
                    $entry->studip_id = $termin->getSingleDateID();
                    $entry->store(); 

                }
            }
            
            $entry = new KuferMapping();
            $entry->studip_id = $sem->getId();
            $entry->store();
            //$entry = KuferMapping::findOneByStudip_id($sem->getId());
            return $entry->ID;
            
            //return $sem->getId();
        } catch (Exception $ex) {
            return new Studip_Ws_Fault($ex->getMessage());
        }
    }

    function update_course_action($api_key, $seminar_id, $seminar_info)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        try {
            $sem = Seminar::getInstance($seminar_id);

            if (isset($seminar_info['title']) && $seminar_info['title']) {
                $sem->name = $seminar_info['title'];
            }

            if (isset($seminar_info['lecture_number']) && $seminar_info['lecture_number']) {
                $sem->seminar_number = $seminar_info['lecture_number'];
            }

            if (isset($seminar_info['institute_id']) && $seminar_info['institute_id']) {
                $sem->institut_id = $seminar_info['institute_id'];
            }
            
            if(isset($seminar_info['description'])){
                $sem->description = $seminar_info['description'];
            }

            $sem->store();
            $sem->setInstitutes(array($sem->institut_id));

            if (isset($seminar_info['lecturers'])) {
                $members = $sem->getMembers('dozent');

                foreach ($seminar_info['lecturers'] as $user) {
                    $entry = KuferMapping::find($user['id']);
                    $user_id = $entry->studip_id;
                    $new_members[$user_id] = $user;
                }

                foreach ($members as $id => $user) {
                    if (!isset($new_members[$id])) {
                        $sem->deleteMember($id);
                    }
                }

                foreach ($new_members as $id => $user) {
                    if (!isset($members[$id])) {
                        $sem->addMember($id, 'dozent', true);
                    }
                }
            }
            
            
            
            //update dates
            foreach ($seminar_info['dates'] as $date) {
                $entry = KuferDateMapping::find($date['id']);
                 
                //wenn es bereits ein entry gibt gehts weiter mit der date_id und die Felder, die aus Kufer kommen 
                //werden für diesen Termin in StudIP modifiziert
                if ($entry) {
                    $date_id = $entry->studip_id;
                    $termin = SingleDate::getInstance($date_id);
                    
                    //freie Raumangabe location
                    if (isset($date['location'])){
                        $termin->setFreeRoomText($date['location']);
                    }
                
                    $termin->setTime($date['begin_time'], $date['end_time']);
                    //wird sich voraussichtlich nicht aus Kufer heraus ändern
                    //$termin->setDateType('1');
                
                    //zugeordnete Dozenten aktualisieren
                    //TODO prüfen ob Dozent schon eingetragen oder nicht
                    //prüfen ob Dozenten entfernt wurden
                    if (isset($date['lecturers'])) {
                        //vgl:
                        //TODO $members = termin get related Persons
                        //ist das ein Array?
                        $members = $termin->getRelatedPersons();
                                
                        foreach ($date['lecturers'] as $user) {
                            $entry = KuferMapping::find($user['id']);
                            $user_id = $entry->studip_id;
                            $new_members[$user_id] = $user;
                        }
                           
                        foreach ($members as $id => $user) {
                            if (!isset($new_members[$id])) {
                                $termin->deleteRelatedPerson($id);
                            }
                        }

                        foreach ($new_members as $id => $user) {
                            if (!isset($members[$id])) {
                                $termin->RelatedPerson($id);
                            }
                        }
                        
                        
                    }
                
                    if(isset($date['description'])){
                        
                        $issues = $termin->getIssueIDs();
                        //find Topic
                        $query = "SELECT issue_id FROM themen WHERE title LIKE :beschreibung AND seminar_id LIKE :sem_id";
                        $statement = DBManager::get()->prepare($query);
                        $statement->execute(array(':beschreibung' => $date['description'], ':sem_id' => $sem->getId()));
                        $topicID = $statement->fetch()[0];

                        //else create topic (Thema) an add
                        if (!$topicID){
                            $issue = new Issue(array('seminar_id' => $sem->getId()));
                            $issue->setTitle($date['description']);
                            $issue->setDescription($date['description']);
                            $issue->store();
                            $topicID = $issue->getIssueID();
                        } 
                        if (!in_array($topicID, $issues)){
                            $termin->addIssueID($topicID);
                        }

                    }

                    $termin->store();
                
                }
                
                //falls es den Termin noch gar nicht gibt wird er erstellt
                else {
                    $termin = new SingleDate(array('seminar_id' => $sem->getId()));

                    //freie Raumangabe location
                    $termin->setFreeRoomText($date['location']);
                
                    $termin->setTime($date['begin_time'], $date['end_time']);
                    $termin->setDateType('1');
                
                    //zugeordnete Dozenten eintragen
                    foreach ($date['lecturers'] as $user) {
                        $entry = KuferMapping::find($user['id']);
                        $user_id = $entry->studip_id;
                        $termin->relatedPerson($user_id);
                    }
                
                    if(isset($date['description'])){
                        //find Topic
                        $query = "SELECT issue_id FROM themen WHERE title LIKE :beschreibung AND seminar_id LIKE :sem_id";
                        $statement = DBManager::get()->prepare($query);
                        $statement->execute(array(':beschreibung' => $date['description'], ':sem_id' => $sem->getId()));
                        $topicID = $statement->fetch()[0];

                        //else create topic (Thema) an add
                        if (!$topicID){
                            $issue = new Issue(array('seminar_id' => $sem->getId()));
                            $issue->setTitle($date['description']);
                            $issue->setDescription($date['description']);
                            $issue->store();
                            $topicID = $issue->getIssueID();
                        } 
                        $termin->addIssueID($topicID);

                    }

                    $termin->store();
                    
                    $entry = new KuferDateMapping($date['id']);
                    $entry->studip_id = $termin->getSingleDateID();
                    $entry->store();                
                }
                
            }
            
            return true;
        } catch (Exception $ex) {
            return new Studip_Ws_Fault($ex->getMessage());
        }
    }

    function delete_course_action($api_key, $seminar_id)
    {
        
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        
        try {
            $sem = Seminar::getInstance($seminar_id);
            $sem->delete();
            $entry->delete();
            return true;
        } catch (Exception $ex) {
            return new Studip_Ws_Fault($ex->getMessage());
        }
    }

    function insert_user_into_course_action($api_key, $seminar_id, $user, $status)
    {
        
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        if(!$seminar_id){ 
         return new Studip_Ws_Fault('Seminar_id not found!');
        }

        try {
            $sem = Seminar::getinstance($seminar_id);
            $entry = KuferMapping::find($user['id']);
            
            if (!$entry){
                $service = new UserService();
                if (!$service->create_studipuser($user)){
                    return new Studip_Ws_Fault('User_id nicht gefunden: ' . $user['id']);
                }
                $entry = KuferMapping::find($user['id']);
            }
            
            $user_id = $entry->studip_id;
            
            $query = "INSERT INTO seminar_user (Seminar_id, user_id, status, position, gruppe, visible, mkdate)
                      VALUES (?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array(
                $sem->id,
                $user_id,
                $status,
                $new_position ?: 0,
                (int)select_group($sem->getSemesterStartTime()),
                in_array($status, words('tutor dozent')) ? 'yes' : 'unknown',
            ));
            
            StudipLog::log('SEM_USER_ADD', $sem->id, $user_id, $status, 'Wurde in die Veranstaltung eingetragen');

            return true;
        } catch (Exception $ex) {
            return new Studip_Ws_Fault($ex->getMessage());
        }
        
    }

    function remove_user_from_course_action($api_key, $seminar_id, $user)
    {
        $entry = KuferMapping::find($seminar_id);
        $seminar_id = $entry->studip_id;
        
        try {
            $sem = Seminar::getInstance($seminar_id);
            $entry = KuferMapping::find($user['id']);
            $user_id = $entry->studip_id;
            $sem->deleteMember($user_id);
            return true;
        } catch (Exception $ex) {
            return new Studip_Ws_Fault($ex->getMessage());
        }
    }

}
