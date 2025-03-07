<?php

/**
 * Scapes message dispatch management class.
 *
 * @copyright  2014 Scapehouse
 */

class Scapes_Model_DbTable_Shmessagedispatch extends Zend_Db_Table_Abstract
{
    protected $_name = "sh_scapes_message_dispatch"; // The table name.
    protected $_schema = "scapes"; // The DB name.

    protected function _setupDatabaseAdapter() 
    {
        $this->_db = Zend_Registry::get($this->_schema);
    }
    
    /**
     * Dispatches a message to a recipient.
     *
     * @param int $threadID The ID of the thread.
     * @param int $senderID The ID of the sender.
     * @param int $senderType The type of the sender.
     * @param int $recipientID The ID of the recipient.
     *
     * @return void
     */
    function dispatchMessage($threadID, $senderID, $senderType, $recipientID)
    {
        $this->_db->insert($this->_name, array(
            "thread_id" => $threadID,
            "sender_id" => $senderID,
            "sender_type" => $senderType,
            "recipient_id" => $recipientID)
        );
    }

    /**
     * Fetches the IDs of all the recipients of a dispatch.
     *
     * @param int $threadID The ID of the thread.
     *
     * @return array The ID array.
     */
    function recipientsForThread($threadID)
    {
        $query = $this->_db->query("
            SELECT recipient_id
            FROM sh_scapes_message_dispatch
            WHERE thread_id = {$threadID}");
        
        $results = $query->fetchAll(PDO::FETCH_COLUMN, 0);

        if ( $results )
        {
            return $results;
        }
    }

    /**
     * Deletes all instances of a dispatch.
     *
     * @param int $threadID The ID of the thread.
     *
     * @return void
     */
    function deleteDispatch($threadID)
    {
        $threadIDQ = $this->_db->quoteInto("?", $threadID);

        $this->_db->delete($this->_name, array(
            "thread_id = ?" => $threadIDQ)
        );
    }
}