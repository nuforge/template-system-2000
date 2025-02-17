<?PHP

class messages extends sitemanager_DB_Object {

    public $primarykey = 'message';

    public function globalQueries() {
        $this->addSelectStatement('message_sent');
        $this->addSelectStatement('member');
        $this->addSelectStatement('member_username');
        $this->addSelectStatement('member_unique');


        //$this->addSelectBoolean('con_visible', 'get_conversations_query');
    }

    public function countUnreadMessages($f_member) {
        $this->joinTable('conversations', 'conversation = message_conversation AND (con_sender = ' . $f_member . ' OR con_recipient = ' . $f_member . ')','RIGHT JOIN');
        $where['message_read'] = 'NULL';
        $where[] = 'message_sender <> ' . $f_member;
        return $this->getCount('DISTINCT message_conversation',$where);
    }




    public function deleteMessage($f_message, $f_member) {
        return $this->update(array('message_deleted' => NOW()), array('message' => $f_message));
    }

    public function readAllUnread($f_conversation, $f_member) {
        $update_set['message_read'] = 'NOW()';
        $update_where['message_conversation'] = $f_conversation;
        $update_where[] = 'message_sender <> '.$f_member;

        return $this->update($update_set, $update_where);
        //$query = 'UPDATE messages SET message_read = now() WHERE message_conversation = ' . $f_conversation . ' AND message_sender <> ' . $f_member . ';';
        //return @pg_query($query);
    }

    public function readMessage($f_message, $f_member) {
        $update_set['message_read'] = 'NOW()';
        $update_where['message'] = $f_message;
        $update_where[] = 'message_sender <> '.$f_member;
        $udpdate_where['message_read'] = 'NULL';
        $this->update($update_set, $update_where);
        return $this->load($f_message);
    }

    public function checkSendBuffer($f_member,$f_limit='1 minute') {
        $select['check'] = "(NOW() - message_sent) < INTERVAL '" . $f_limit . "'";
        $where['message'] = "(SELECT max(message) FROM messages WHERE message_sender =" . $f_member . ")";
        $re = @pg_fetch_assoc(@pg_query($this->formatQuery($select, false, $where)));
        return $this->bool2bool($re['check']);
    }

    public function getList($f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        $this->joinTable('members', 'message_sender = member');
        return @pg_fetch_all(@pg_query($this->formatQuery($this->defaultQuery, $f_order, $f_where, $f_limit, $f_offset, $f_table)));
    }
    
    public function getCount($f_column=false, $f_where=false, $f_group=false, $f_table=false) {
        if (empty($f_column) && !is_array($this->primarykey)) {
            $f_column = $this->primarykey;
        } elseif (empty($f_column) && is_array($this->primarykey)) {
            $f_column = 0;
        }
        $query = 'count(' . $f_column . ') as count';
        //echo $this->formatQuery($query, false, $f_where, false, false, $f_table);
        $queryResult = @pg_fetch_assoc(@pg_query($this->formatQuery($query, false, $f_where, false, false, $f_table)));
        return $queryResult['count'];
    }

}

?>