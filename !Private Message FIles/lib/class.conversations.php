<?PHP

class conversations extends sitemanager_DB_Object {
    
    public $primarykey = 'conversation';
    protected $readingMember;


    public function globalQueries() {
        $this->addSelectStatement('count(DISTINCT conversation)', 'conversation_count', 'count_conversations_query');

        $this->addSelectStatement('case when con_deleter IS NOT NULL THEN 1 ELSE 0 END', 'con_deleted');
        $this->addSelectBoolean('con_flagged');
        $this->addSelectBoolean('con_visible');
        
        $this->addSelectStatement('conversation', false, 'get_conversations_query');
        $this->addSelectStatement('con_subject', false,  'get_conversations_query');
        $this->addSelectStatement('member_username', false,  'get_conversations_query');
        $this->addSelectStatement('member_unique', false,  'get_conversations_query');
        $this->addSelectStatement('photo', false,  'get_conversations_query');
        $this->addSelectStatement('CASE WHEN con_flagged THEN 1 ELSE 0 END', 'con_flagged',  'get_conversations_query');
        $this->addSelectStatement('message', false,  'get_conversations_query');
        $this->addSelectStatement('message_sent', false,  'get_conversations_query');

        $this->addSelectStatement('case when con_deleter IS NOT NULL THEN 1 ELSE 0 END', 'con_deleted', 'get_conversations_query');
        $this->addSelectBoolean('con_flagged', 'get_conversations_query');
        $this->addSelectBoolean('con_visible', 'get_conversations_query');
        

    }

    public function globalJoins() {

    }


    public function getList($f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        //$this->debugOut($this->formatQuery($this->defaultQuery, $f_order, $f_where, $f_limit, $f_offset, $f_table));
        return @pg_fetch_all(@pg_query($this->formatQuery($this->defaultQuery, $f_order, $f_where, $f_limit, $f_offset, $f_table)));
    }



    public function setPrimaryReader($f_member) {
        $this->readingMember = $f_member;
        $this->addSelectStatement('(SELECT CASE WHEN count(message) >=1 THEN 1 else 0 END FROM messages WHERE message_conversation = conversation AND message_sender <> ' . $f_member . ' AND message_read IS NULL)', 'unread');
        $this->addSelectStatement('CASE WHEN message_sender = ' . $f_member . ' THEN 1 ELSE 0 END', 'message_sent_by_member');
        $this->joinTable('members', '(member = con_sender OR member = con_recipient) AND member <> ' . $f_member);
        $this->joinTable('messages', 'message = (SELECT max(message) FROM messages WHERE message_conversation = conversation)');
    }
    public function getPrimaryReader() {
        return $this->readingMember;
    }

    //TODO deleteConversation
    public function deleteConversation($f_conversation, $f_member) {
        $conversation = $this->load(array('conversation' => $f_conversation), $f_member);
        if (!$conversation) {
            return false;
        }
        //IF deleter IS NOT NULL
        if (!empty($conversation['con_deleted'])) {
            if (!empty($conversation['con_deleter']) && $conversation['con_deleter'] != $f_member) {
                return $this->update(array('con_visible' => 'false'), array('conversation' => $f_conversation));
            }
        } else { //IF deleter is NULL se Deleter
            return $this->update(array('con_deleter' => $f_member), array('conversation' => $f_conversation));
        }

        return false;
    }



    public function checkConversation($f_conversation, $f_member) {
        $where['conversation'] = $f_conversation;
        $where[] = '(con_sender = ' . $f_member . ' OR con_recipient = ' . $f_member . ')';
        $result = $this->load($where);
        return !empty($result);
    }
    

}

?>