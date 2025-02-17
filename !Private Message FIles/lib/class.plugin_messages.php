<?php

class plugin_messages extends sitemanager_page {

    public function localInitialize() {
        $this->setPluginName('messages');
        $this->setPageVariable($this->getPageVariable(PAGE_MAINURL) . $this->getPluginName .'/', PAGE_METAURL);
    }

    public function localReinitialize() {

        $this->SITEMANAGER->checkPrivileges($this->member['member']);
        $this->addTemplateDirectory($this->getPluginName());
        $this->PRIVATE_MESSAGE_MANAGER->checkForUnreadMessages();
        $this->addScriptfile('messages.js');
        $this->currentNavTab['messages'] = 'current';
        
    }

    public function site_index() {
        $showPerPage = 25;
        $pageNumber = (!empty($_GET['page'])) ? $_GET['page'] : 1;

        $this->appendTitle('Messages - Page ' . $pageNumber);
        if (!empty($_POST)) {
            if (!$this->PRIVATE_MESSAGE_MANAGER->validateDeleteConversations($_POST['message_selector'])) {

                $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage();
                $this->fillForms($_POST);
            } else {
                if ($this->PRIVATE_MESSAGE_MANAGER->deleteConversations($_POST['message_selector'])) {
                    $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage('success');
                } else {
                    $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage();
                    $this->fillForms($_POST);
                }
            }
        }
        $favorite = false;
        switch ($_GET['status']) {
            case 'sent':
                $this->appendTitle('Sent',' ');
                $this->current['sent'] = 'current';
                $this->totalMessages = $this->PRIVATE_MESSAGE_MANAGER->countConversations('sent',$this->member['member']);
                $this->messageList = $this->PRIVATE_MESSAGE_MANAGER->getConversations($pageNumber, $showPerPage, 'sent');
                $this->pagination = $this->getPagination($pageNumber, $showPerPage, $this->totalMessages);
                break;
            case 'read':
                $this->appendTitle('Read',' ');
                $this->current['read'] = 'current';
                //$this->messagelist = $this->SITEMANAGER->getReadMessages($this->member['member'],$pageNumber,$showPerPage);
                break;
            case 'favorite':
                $this->current['favorite'] = 'current';
                $this->totalMessages = $this->PRIVATE_MESSAGE_MANAGER->countConversations('favorite');
                $this->messageList = $this->PRIVATE_MESSAGE_MANAGER->getConversations($pageNumber, $showPerPage, 'favorite');
                $this->pagination = $this->getPagination($pageNumber, $showPerPage, $this->totalMessages);

                break;
            case 'archived':
                $this->current['archive'] = 'current';
                $this->totalMessages = $this->PRIVATE_MESSAGE_MANAGER->countConversations('archive');
                $this->messageList = $this->PRIVATE_MESSAGE_MANAGER->getConversations($pageNumber, $showPerPage, 'archive');
                $this->pagination = $this->getPagination($pageNumber, $showPerPage, $this->totalMessages);

                break;
            default:
                $this->current['inbox'] = 'current';
                $this->totalMessages = $this->PRIVATE_MESSAGE_MANAGER->countConversations($favorite);
                $this->messageList = $this->PRIVATE_MESSAGE_MANAGER->getConversations($pageNumber, $showPerPage, $favorite);
                $this->pagination = $this->getPagination($pageNumber, $showPerPage, $this->totalMessages);

                break;
        }
        $this->output();
    }

    public function site_compose() {
        $this->appendTitle('Compose Message');
        $this->current['new'] = 'current';
        /*$favorites = $this->SITEMANAGER->getFavoritesSimple($this->member['member']);
        if (!empty($favorite)) {
            $this->setFormElementOptions('message_favorites', array(0 => '--Member Quicklist--') + $favorites);
        } else {
             $this->setFormElementOptions('message_favorites', array(0 => '--Member Quicklist--'));
        }*/
        if (!empty($_GET['username'])) {
            $member = $this->SITEMANAGER->getMember($_GET['username']);
            $this->setFormElementValue('message_username', $member['member_username']);
            $this->setFormElementValue('message_unique', $member['member_unique']);
        }
        if (!empty($_POST)) {
            if (!empty($_POST['message_username'])) {
                $_POST['message_unique'] = $this->encodeString($_POST['message_username']);
            }
            if (!$this->PRIVATE_MESSAGE_MANAGER->validateNewMessage($_POST['message_unique'], $_POST['message_subject'], $_POST['message_body'])) {
            
                $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage();
                $this->fillForms($_POST);
            } else {
                $conversation_id = $this->PRIVATE_MESSAGE_MANAGER->sendNewMessage($_POST, $_POST['message_unique']);
                if (!empty($conversation_id)) {
                    header('location: /messages/' . $conversation_id);
                    $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage('success');
                    unset($_POST);
                } else {
                    $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage();
                    $this->fillForms($_POST);
                }
            }
        }
        $this->output();
    }

    public function site_message() {
        $this->appendTitle('Read Message');

        //TODO FIX issue with repicient not showing up correctly.
        $this->sender = false;

        if (!empty($_POST)) {
            if (!$this->PRIVATE_MESSAGE_MANAGER->validateReply($_POST['new_message_body'], $_POST['conversation_id'])) {

                $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage();
            } else {
                if ($this->PRIVATE_MESSAGE_MANAGER->sendReply($_POST['new_message_body'], $_POST['conversation_id'])) {
                    $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage('success');
                } else {
                    $this->status_message = $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage();
                }
            }
        }

        $this->conversation = $this->PRIVATE_MESSAGE_MANAGER->readConversation($_GET['message']);
        $this->new_messages = $this->PRIVATE_MESSAGE_MANAGER->checkForUnreadMessages();
        if ($this->conversation['con_sender'] == $this->member['member']) {
            $this->sender = true;
        }
        $this->setFormElementValue('conversation_id', $this->conversation['conversation']);

        $this->output();
    }

}

?>