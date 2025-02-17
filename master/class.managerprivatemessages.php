<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ManagerPrivateMessages extends Manager {

    protected $connection_info;
    protected $database_connection;
    protected $messageHandler;
    protected $redirectURL = '/login.html';
    protected $privilegeChecks = array();
    protected $memberPrivileges = array();
    
    protected $memberID = 'member';
    protected $memberUniqueName = 'mem_unique';


    protected function initialize_ManagerPrivateMessages() {

        DEFINE('PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME', 'conversations');
        DEFINE('PRIVATE_MESSAGES_MESSAGES_CLASS_NAME', 'messages');
        DEFINE('PRIVATE_MESSAGES_FOLDERS_CLASS_NAME', 'conversation_folders');
        DEFINE('PRIVATE_MESSAGES_MEMBERS_CLASS_NAME', 'members');
        DEFINE('PRIVATE_MESSAGES_BLACKLIST_CLASS_NAME', 'blocked_members');
        DEFINE('PRIVATE_MESSAGES_WHITELIST_CLASS_NAME', 'whitelist');

        DEFINE('PRIVATE_MESSAGE_TYPE_MESSAGE', 1);
        DEFINE('PRIVATE_MESSAGE_TYPE_WINK', 2);

        DEFINE('PRIVATE_MESSAGE_FOLDER_ARCHIVE', 'archive');
        DEFINE('PRIVATE_MESSAGE_FOLDER_DELETE', 'delete');
        DEFINE('PRIVATE_MESSAGE_FOLDER_FAVORITE', 'favorite');
        DEFINE('PRIVATE_MESSAGE_FOLDER_SENT', 'sent');

        DEFINE('PRIVATE_MESSAGE_ATTRIBUTE_IS_READER', 'is_reader');
        DEFINE('PRIVATE_MESSAGE_CLASS_IS_READER', 'reader');
        DEFINE('PRIVATE_MESSAGE_ATTRIBUTE_NOT_READER', 'not_reader');
        DEFINE('PRIVATE_MESSAGE_CLASS_NOT_READER', 'not_reader');

        DEFINE('PRIVATE_MESSAGE_MESSAGES_FIELD_NAME', 'messages');
        DEFINE('PRIVATE_MESSAGE_FOLDERS_FIELD_NAME', 'folders');

        DEFINE('PRIVATE_MESSAGE_MAX_SUBJECT_COUNT', '140');
        DEFINE('PRIVATE_MESSAGE_MAX_BODY_COUNT', '1000');

        DEFINE('PRIVATE_MESSAGE_RETURN_FILTER', true);
        DEFINE('PRIVATE_MESSAGE_RETURN_NO_FILTER', false);
    }




    public function setPrimaryMember($f_member) {
        $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);
        $member = $obj_members->load($f_member);
        if (empty($member)) {
            return false;
        }
        $this->myPrimaryMember = $member['member'];
        $this->myCurrentMember = $member['member'];
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);
        $obj_conversations->setPrimaryReader($member['member']);
        return $this->myPrimaryMember;
    }

    public function checkContacted($f_recipient, $f_messageType=false, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_messages = $this->loadClass(PRIVATE_MESSAGES_MESSAGES_CLASS_NAME);
        $obj_messages->joinTable('conversations', 'message_conversation = conversation');
        $where['message_sender'] = $this->myCurrentMember;
        $where['con_recipient'] = $f_recipient;
        if ($f_messageType) {
            $where['message_type'] = $messageType;
        }
        return $obj_messages->getCount('messages', $where);
    }

    public function checkForUnreadMessages($f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_messages = $this->loadClass(PRIVATE_MESSAGES_MESSAGES_CLASS_NAME);
        $unreadMessages = $obj_messages->countUnreadMessages($this->myCurrentMember);
        return (empty($unreadMessages)) ? false : $unreadMessages;
    }


    public function countUnreadMessages($f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_messages = $this->loadClass(PRIVATE_MESSAGES_MESSAGES_CLASS_NAME);
        $unreadMessages = $obj_messages->countUnreadMessages($this->myCurrentMember);
        return $unreadMessages;
    }

    public function validateReply($f_message, $f_conversation, $f_reader=false) {
        $this->checkCurrentMember($f_reader);

        $cleanMessage = $this->cleanString($f_message);
        if (empty($cleanMessage)) {
            $this->addStatusMessage('Message Body is empty.');
        }
        if (strlen($cleanMessage) > PRIVATE_MESSAGE_MAX_BODY_COUNT) {
            $this->addStatusMessage('Message Body too long. Text must be less than ' . PRIVATE_MESSAGE_MAX_BODY_COUNT . ' characters.');
        }

        return ($this->checkStatusMessage()) ? false : true;
    }

    public function sendReply($f_message, $f_conversation, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_messages = $this->loadClass(PRIVATE_MESSAGES_MESSAGES_CLASS_NAME);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);

        $where['conversation'] = $f_conversation;
        $where[] = '(con_sender = ' .  $this->myCurrentMember . ' OR con_recipient = ' . $this->myCurrentMember . ')';
        $conversation = $obj_conversations->load($where);
        if (!$conversation || !$obj_conversations->checkConversation($conversation['conversation'], $this->myCurrentMember)) {
            $this->addStatusMessage('Invalid Conversation');
            return false;
        }
        $new_message['message_conversation'] = $conversation['conversation'];
        $new_message['message_sender'] = $this->myCurrentMember;
        $new_message['message_body'] = $this->cleanString($f_message);
        $new_message['message_type'] = PRIVATE_MESSAGE_TYPE_MESSAGE;

        if ($obj_messages->insert_return($new_message)) {
            $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);
            $this->addStatusMessage('Message successfully sent.', MESSAGE_STATUS_TYPE_SUCCESS);
            $sender = $obj_members->load($this->myCurrentMember);
            $recipient = ($conversation['con_sender'] == $this->myCurrentMember) ? $this->loadMemberByUnique($conversation['con_recipient']) : $this->loadMemberByUnique($conversation['con_sender']);
            if ($recipient['mem_settings_notification_new_message']) {
                $new_email['message_id'] = $new_message['message_conversation'];
                $new_email['mainurl'] = $this->pageData['mainurl'];
                $new_email['email'] = $recipient['mem_email'];
                $new_email['username'] = $recipient['mem_username'];
                $new_email['sender'] = $sender['mem_username'];
                $new_email['message_sample'] = substr($new_message['message_body'], 0, 140) . '...';
                $new_email['phpversion'] = phpversion();


                $this->sendNotificationEmail('notify-new-message', $new_email);
            }
            return true;
        } else {
            $this->addStatusMessage('Unable to send reply. Please contact support.');
            return false;
        }
    }

    public function validateNewMessage($f_recipient, $f_subject, $f_message, $f_reader=false) {

        $this->checkCurrentMember($f_reader);
        $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);
        $cleanSubject = $this->cleanString($f_subject);
        $cleanMessage = $this->cleanString($f_message);
        if (strlen($cleanSubject) > MAX_SMALL_TEXT_COUNT) {
            $this->addStatusMessage('Message subject must be less than ' . MAX_SMALL_TEXT_COUNT . ' characters.');
            return false;
        }
        if (strlen($cleanMessage) > PRIVATE_MESSAGE_MAX_BODY_COUNT) {
            $this->addStatusMessage('Message body must be less than ' . PRIVATE_MESSAGE_MAX_BODY_COUNT . ' characters.');
            return false;
        }
        if (empty($cleanSubject)) {
            $this->addStatusMessage('Subject cannot be empty.');
            return false;
        }
        if (empty($cleanMessage)) {
            $this->addStatusMessage('Message body cannot be empty.');
            return false;
        }
        if (!$this->myCurrentMember) {
            $this->addStatusMessage('Sender is an invalid member.');
            return false;
        }
        if (!$this->loadMemberByUnique($f_recipient)) {
            
            $this->addStatusMessage('Recipient is an invalid member.');
            return false;
        }
        //return false;
        return true;
    }

    public function sendNewMessage($fa_message_details, $f_recipient, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);
        $obj_messages = $this->loadClass(PRIVATE_MESSAGES_MESSAGES_CLASS_NAME);
        $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);

        if (empty($f_recipient)) {
            $this->addStatusMessage('Please select a recipient for this message.');
            return false;
        }

        $sender = $obj_members->load($this->myCurrentMember);
        $recipient = $this->loadMemberByUnique($f_recipient);
        $new_conversation['con_sender'] = $this->myCurrentMember;
        $new_conversation['con_recipient'] = $recipient['member'];
        $new_conversation['con_subject'] = $this->cleanString($fa_message_details['message_subject']);

        $new_conversation['con_flagged'] = $this->checkMessageFilters($recipient['member']);

        if (empty($fa_message_details['message_type'])) {
            $new_message['message_type'] = PRIVATE_MESSAGE_TYPE_MESSAGE;
        } else {
            $new_message['message_type'] = $fa_message_details['message_type'];
        }
        $new_message['message_sender'] = $this->myCurrentMember;
        $new_message['message_body'] = $this->cleanString($fa_message_details['message_body']);
        $new_message['message_conversation'] = $obj_conversations->insert_return($new_conversation);
        if (empty($new_message['message_conversation'])) {
            $this->addStatusMessage('Unable to create conversation. Please contact support.');
            return false;
        }
        $message_id = $obj_messages->insert_return($new_message);
        if (!$message_id) {
            $this->addStatusMessage('Unable to send message. Please contact support.');
            return false;
        }


        if ($recipient['mem_settings_notification_new_message']) {
            $new_email['message_id'] = $new_message['message_conversation'];
            $new_email['mainurl'] = $this->pageData['mainurl'];
            $new_email['email'] = $recipient['mem_email'];
            $new_email['username'] = $recipient['mem_username'];
            $new_email['sender'] = $sender['mem_username'];
            $new_email['message_sample'] = substr($new_message['message_body'], 0, 140) . '...';
            $new_email['phpversion'] = phpversion();


            $this->sendNotificationEmail('notify-new-message', $new_email);
        }


        $this->addStatusMessage('Message successfully sent.', MESSAGE_STATUS_TYPE_SUCCESS);
        return $new_message['message_conversation'];
    }

    public function validateArchiveConversations($f_conversation, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);


        return true;
    }

    public function toggleDeleteConversation($f_conversation, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
//TODO Mark Conversation as read.
        $this->toggleConversationFolder($f_conversation, MESSAGE_FOLDER_DELETE, $f_reader);
    }

    public function toggleFavoriteConversation($f_conversation, $f_reader=false) {
        $this->toggleConversationFolder($f_conversation, MESSAGE_FOLDER_FAVORITE, $f_reader);
    }

    public function toggleArchiveConversation($f_conversation, $f_reader=false) {
        $this->toggleConversationFolder($f_conversation, MESSAGE_FOLDER_ARCHIVE, $f_reader);
    }

    public function toggleConversationFolder($f_conversation, $f_folder, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);
        $obj_conversation_folders = $this->loadClass(PRIVATE_MESSAGES_FOLDERS_CLASS_NAME);
        $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);
        $conversation = $obj_conversations->load($f_conversation);
        if (empty($conversation)) {
            $this->addStatusMessage('Conversation or Member ID invalid');
            return false;
        }
        $new_folder['cf_member'] = $this->myCurrentMember;
        $new_folder['cf_conversation'] = $conversation['conversation'];
        $new_folder['cf_folder'] = $f_folder;

        $old_check = $obj_conversation_folders->load($new_folder);

        if ($old_check) {
            if ($obj_conversation_folders->removeFromFolder($this->myCurrentMember, $conversation['conversation'], $f_folder)) {
                return '2';
            } else {
                $this->addStatusMessage('Unable to remove conversation from Folder');
                return false;
            }
        }
        if ($obj_conversation_folders->insert_return($new_folder)) {
            return '1';
        } else {
            $this->addStatusMessage('Unable to move conversation to Folder');
            return false;
        }
    }

    public function countConversations($f_folder=false, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);

        $where[] = '(con_sender = ' . $this->myCurrentMember . ' OR con_recipient = ' . $this->myCurrentMember . ')';
        $where['con_visible'] = 'true';
        $where[] = '(con_deleter IS NULL OR con_deleter <> ' . $this->myCurrentMember . ')';
        $where[] = 'conversation NOT IN (SELECT cf_conversation FROM conversation_folders WHERE cf_conversation = conversation and cf_member = ' . $this->myCurrentMember . " AND cf_folder =  'delete')";

        $folderWhere = $this->checkFolderOptions($f_folder, $this->myCurrentMember);
        if (is_array($folderWhere)) {
            $where = $where + $folderWhere;
        }

        return $obj_conversations->getCount('DISTINCT conversation', $where);
    }

    public function getConversations($f_page_number, $f_show_per_page, $f_folder=false, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);
        $obj_conversation_folders = $this->loadClass(PRIVATE_MESSAGES_FOLDERS_CLASS_NAME);
        $folder = $obj_conversation_folders->validateValues(array('cf_folder' => $f_folder));
        $offset = (($f_page_number - 1) * $f_show_per_page);

        $where[] = '(con_sender = ' . $this->myCurrentMember . ' OR con_recipient = ' . $this->myCurrentMember . ')';
        $where['con_visible'] = 'true';
        $where[] = 'conversation NOT IN (SELECT cf_conversation FROM conversation_folders WHERE cf_conversation = conversation and cf_member = ' . $this->myCurrentMember . " AND cf_folder =  'delete')";

        $folderWhere = $this->checkFolderOptions($f_folder, $this->myCurrentMember);
        if (is_array($folderWhere)) {
            $where = array_merge($where, $folderWhere);
        }

        $order['message_sent'] = 'DESC';
        //var_dump($where);
        $conversations = $obj_conversations->getList($order, $where, $f_show_per_page, $offset);
        
        if (!$conversations) {
            return false;
        }
        $folder_where['cf_member'] = $this->myCurrentMember;
        foreach ($conversations as $k => $conversation) {
            $folder_where['cf_conversation'] = $conversation['conversation'];

            $conversations[$k][PRIVATE_MESSAGE_FOLDERS_FIELD_NAME] = $obj_conversation_folders->getFolderList('cf_folder', 'cf_folder', $folder_where);
        }

        return $conversations;
    }

    public function readConversation($f_conversation, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);
        $obj_messages = $this->loadClass(PRIVATE_MESSAGES_MESSAGES_CLASS_NAME);
        $conversation = $obj_conversations->load($f_conversation);
        if (empty($conversation) || ($conversation['con_sender'] != $this->myCurrentMember && $conversation['con_recipient'] != $this->myCurrentMember)) {
            return false;
        }
        $conversation[PRIVATE_MESSAGE_MESSAGES_FIELD_NAME] = $obj_messages->getList('message_sent', array('message_conversation' => $conversation['conversation']));
        $obj_messages->readAllUnread($conversation['conversation'], $this->myCurrentMember);

        foreach ($conversation[PRIVATE_MESSAGE_MESSAGES_FIELD_NAME] as $k => $v) {
            if ($v['member'] == $this->myCurrentMember) {
                $conversation[PRIVATE_MESSAGE_MESSAGES_FIELD_NAME][$k][PRIVATE_MESSAGE_ATTRIBUTE_IS_READER] = true;
                $conversation[PRIVATE_MESSAGE_MESSAGES_FIELD_NAME][$k]['class'] = PRIVATE_MESSAGE_CLASS_IS_READER;
            } else {
                $conversation[PRIVATE_MESSAGE_MESSAGES_FIELD_NAME][$k][PRIVATE_MESSAGE_ATTRIBUTE_NOT_READER] = true;
                $conversation[PRIVATE_MESSAGE_MESSAGES_FIELD_NAME][$k]['class'] = PRIVATE_MESSAGE_CLASS_NOT_READER;
            }
        }
        return $conversation;
    }

    public function validateDeleteConversations($fa_conversations, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);
// Check conversations.

        if (is_array($fa_conversations)) {
            foreach ($fa_conversations as $conversation) {
                if ($obj_conversations->checkConversation($conversation, $this->myCurrentMember)) {
                    $this->addStatusMessage('Invalid Conversation: ' . $conversation . '. If trouble persists, contact support');
                }
            }
        } else {
            if ($obj_conversations->checkConversation($fa_conversations, $this->myCurrentMember)) {
                $this->addStatusMessage('Invalid Conversation: ' . $conversation . '. If trouble persists, contact support');
            }
        }
        return true;
    }

    public function deleteConversations($fa_conversations, $f_reader=false) {
        $this->checkCurrentMember($f_reader);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);

        $deletedConversationCount = 0;
        if (is_array($fa_conversations)) {
            foreach ($fa_conversations as $conversation) {
                if ($obj_conversations->deleteConversation($conversation, $this->myCurrentMember)) {
                    $deletedConversationCount++;
                } else {
                    $this->addStatusMessage('Unable to delete conversation: ' . $conversation . '. If trouble persists, contact support', MESSAGE_STATUS_TYPE_SUCCESS);
                }
            }
            $this->addStatusMessage($deletedConversationCount . ' messages deleted.', MESSAGE_STATUS_TYPE_SUCCESS);
        } else {
            if ($obj_conversations->deleteConversation($fa_conversations, $this->myCurrentMember)) {
                $this->addStatusMessage('Message deleted.', MESSAGE_STATUS_TYPE_SUCCESS);
                return true;
            } else {
                $this->addStatusMessage('Unable to delete conversation. If trouble persists, contact support', MESSAGE_STATUS_TYPE_SUCCESS);
            }
        }
        return true;
    }

    public function loadMemberByUnique($f_member_unique) {
        $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);
        if (is_numeric($f_member_unique)) {
            $where[$this->memberID] = $f_member_unique;
        } else {
            $where[$this->memberUniqueName] = $this->encodeString($f_member_unique);
        }
        return $obj_members->load($where);
    }

    public function checkFolderOptions($f_folder, $f_reader=false) {
        $this->checkCurrentMember($f_reader);

        if ($f_folder) {
            switch ($f_folder) {
                case PRIVATE_MESSAGE_FOLDER_SENT:
                    //$where[] = 'con_sender = ' . $this->myCurrentMember . ' ';
                    $where [] = $this->myCurrentMember . ' = (SELECT message_sender from messages WHERE message_conversation = conversation ORDER BY message_sent DESC LIMIT 1)';
                    break;
                default:
                    $where[] = 'conversation IN (SELECT cf_conversation FROM conversation_folders WHERE cf_conversation = conversation and cf_member = ' . $this->myCurrentMember . " AND cf_folder = '" . $f_folder . "')";
                    $not_archived = true;
                    break;
            }
        }
        if (!$not_archived) {
            $where[] = 'conversation NOT IN (SELECT cf_conversation FROM conversation_folders WHERE cf_conversation = conversation and cf_member = ' . $this->myCurrentMember . " AND cf_folder =  'archive')";
        }

        return $where;
    }

    public function checkSendBuffer($f_reader) {
        $this->checkCurrentMember($f_reader);
        $obj_messages = $this->loadClass(PRIVATE_MESSAGES_MESSAGES_CLASS_NAME);
        return $obj_messages->checkSendBuffer($this->myCurrentMember);
    }

    // RETURN TRUE TO APPLY A FLAG TO MESSAGE
    public function checkMessageFilters($f_recipient, $f_sender=false) {
        $this->checkCurrentMember($f_sender);
        $obj_conversations = $this->loadClass(PRIVATE_MESSAGES_CONVERSATIONS_CLASS_NAME);
        $obj_members = $this->loadClass(PRIVATE_MESSAGES_MEMBERS_CLASS_NAME);
        /* $obj_whitelists = $this->loadClass('PRIVATE_MESSAGES_WHITELIST_CLASS_NAME');
          $obj_blacklists = $this->loadClass('PRIVATE_MESSAGES_BLACKLIST_CLASS_NAME');

          //$conversation = $obj_conversation->load($f_conversation);
          $recipient = $obj_members->load($f_recipient);
          if (empty($recipient)) {
          return false;
          }
          $sender = $this->loadProfile($this->myCurrentMember, $recipient['member']);
          if (empty($sender)) {
          return false;
          }


          if ($obj_whitelists->checkForUser($recipient['member'], $sender['member'])) {
          return PRIVATE_MESSAGE_RETURN_NO_FILTER;
          }
          //WHITELIST OPTIONS
          //FAVORITE WHITELIST
          if ($recipient['mem_settings_whitelist_favorites'] && $sender['is_favorite']) {
          return PRIVATE_MESSAGE_RETURN_NO_FILTER;
          }
          //FRIEND WHITELIST
          if ($recipient['mem_settings_whitelist_friends'] && $sender['are_friends']) {
          return PRIVATE_MESSAGE_RETURN_NO_FILTER;
          }
          if ($recipient['mem_settings_whitelist_messaged'] && $sender['have_contacted']) {
          return PRIVATE_MESSAGE_RETURN_NO_FILTER;
          }
          if ($recipient['mem_settings_whitelist_premium'] && $sender['mem_premium']) {
          return PRIVATE_MESSAGE_RETURN_NO_FILTER;
          }
          if ($recipient['mem_settings_whitelist_distance'] && $sender['distance'] < $recipient['mem_settings_whitelist_distance']) {
          return PRIVATE_MESSAGE_RETURN_NO_FILTER;
          }


          //BLACKLIST LIST
          if ($obj_blacklists->checkForUser($recipient['member'], $sender['member'])) {
          return PRIVATE_MESSAGE_RETURN_FILTER;
          }
          //PHOTOS FILTER
          if ($recipient['mem_settings_message_filter_photos'] && $sender['mem_number_photos'] < $recipient['mem_settings_message_filter_photos']) {
          return PRIVATE_MESSAGE_RETURN_FILTER;
          }
          if ($recipient['mem_settings_message_filter_distance'] && $sender['distance'] > $recipient['mem_settings_message_filter_distance']) {
          return PRIVATE_MESSAGE_RETURN_FILTER;
          }
          if ($recipient['mem_settings_message_filter_joined'] && $sender['mem_days_as_member'] > $recipient['mem_settings_message_filter_joined']) {
          return PRIVATE_MESSAGE_RETURN_FILTER;
          }
          if ($recipient['mem_settings_message_filter_min_age'] && $sender['mem_age'] < $recipient['mem_settings_message_filter_min_age']) {
          return PRIVATE_MESSAGE_RETURN_FILTER;
          }
          if ($recipient['mem_settings_message_filter_max_age'] && $sender['mem_age'] > $recipient['mem_settings_message_filter_max_age']) {
          return PRIVATE_MESSAGE_RETURN_FILTER;
          }
          if ($recipient['mem_settings_message_filter_must_be_single'] && $sender['mem_relationship_status'] != 17) {

          return PRIVATE_MESSAGE_RETURN_FILTER;
          }

         *
         */

        return PRIVATE_MESSAGE_RETURN_NO_FILTER;
    }
    
    public function setMemberUniqueName($f_new_member_unique_name) {
        $this->memberUniqueName = $f_new_member_unique_name;
    }

}

?>
