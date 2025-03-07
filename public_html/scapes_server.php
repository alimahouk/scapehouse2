#!/usr/bin/php5

<?php

/**
 * Scapes Chat Server.
 *
 * @copyright  2014 Scapehouse
 *
 * Local path: #!/Applications/MAMP/bin/php/php5.4.10/bin/php
 * Deployment path: #!/usr/bin/php5
 */

require_once 'autoload.php';

    /* ZEND FRAMEWORK */

    // Define path to application directory.
    defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
    
    // Define application environment.
    defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
    
    // Ensure library/ is on include_path.
    set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/../library') , get_include_path())));
    
    /** Zend_Application */
    require_once 'Zend/Application.php';
    
    // Create application, bootstrap, and run.
    $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
    $application->bootstrap()->run();

    // --------------------------
    /*! @class      SocketServer
        @author     Navarr Barnier
        @abstract   A Framework for creating a multi-client server using the PHP language.
     */
    class SocketServer
    {
        /*! @var        config
            @abstract   Array - an array of configuration information used by the server.
         */
        protected $config;
 
        /*! @var        hooks
            @abstract   Array - a dictionary of hooks and the callbacks attached to them.
         */
        protected $hooks;
 
        /*! @var        master_socket
            @abstract   resource - The master socket used by the server.
         */
        protected $master_socket;
        
        /*! @var        connected_clients
            @abstract   unsigned int - The number of clients currently connected.
         */
        public $connected_clients = 0;
 
        /*! @var        max_read
            @abstract   unsigned int - The maximum number of bytes to read from a socket at a single time.
         */
        public $max_read = 6144;
 
        /*! @var        clients
            @abstract   Array - an array of connected clients.
         */
        public $clients;

        /*! @var        multipleInstanceClients
            @abstract   Array - an array of clients connected with more than one instance.
         */
        public $multipleInstanceClients;

        /*! @function   __construct
            @abstract   Creates the socket and starts listening to it.
            @param      string  - IP Address to bind to, NULL for default.
            @param      int - Port to bind to
            @result     void
         */
        public function __construct($bind_ip, $port)
        {
            set_time_limit(0);
            ob_implicit_flush();

            $this->hooks = array();
            
            $this->config["ip"] = $bind_ip;
            $this->config["port"] = $port;
            
            $this->multipleInstanceClients = array();

            $this->master_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() failed!\n");
            socket_set_option($this->master_socket, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_option(SO_REUSEADDR) failed!\n");
            socket_set_option($this->master_socket, SOL_TCP, TCP_NODELAY, 1) or die("socket_option(TCP_NODELAY) failed!\n");
            socket_bind($this->master_socket, $this->config["ip"], $this->config["port"]) or die("Issue Binding!\n");
            socket_getsockname($this->master_socket, $bind_ip,$port);
            socket_listen($this->master_socket);
            SocketServer::debug("Listenting for connections on {$bind_ip}:{$port}");
        }
 
        /*! @function   hook
            @abstract   Adds a function to be called whenever a certain action happens.  Can be extended in your implementation.
            @param      string  - Command
            @param      callback- Function to Call.
            @see        unhook
            @see        trigger_hooks
            @result     void
         */
        public function hook($command, $function)
        {
            $command = strtoupper($command);

            if ( !isset($this->hooks[$command]) )
            {
                $this->hooks[$command] = array();
            }

            $k = array_search($function, $this->hooks[$command]);

            if ( $k === FALSE )
            {
                $this->hooks[$command][] = $function;
            }
        }
 
        /*! @function   unhook
            @abstract   Deletes a function from the call list for a certain action.  Can be extended in your implementation.
            @param      string  - Command
            @param      callback- Function to Delete from Call List
            @see        hook
            @see        trigger_hooks
            @result     void
         */
        public function unhook($command = NULL, $function)
        {
            $command = strtoupper($command);

            if ( $command !== NULL )
            {
                $k = array_search($function, $this->hooks[$command]);

                if ( $k !== FALSE )
                {
                    unset($this->hooks[$command][$k]);
                }
            }
            else
            {
                $k = array_search($this->user_funcs,$function);

                if ( $k !== FALSE )
                {
                    unset($this->user_funcs[$k]);
                }
            }
        }
 
        /*! @function   loop_once
            @abstract   Runs the class's actions once.
            @discussion Should only be used if you want to run additional checks during server operation.  Otherwise, use infinite_loop()
            @param      void
            @see        infinite_loop
            @result     bool    - True
        */
        public function loop_once()
        {
            // Set up client's listen socket for reading.
            $read[0] = $this->master_socket;

            for ( $i = 0; $i < $this->connected_clients; $i++ )
            {
                if ( isset($this->clients[$i]) )
                {
                    $read[$i + 1] = $this->clients[$i]->socket;
                }
            }
            
            // Set up a blocking call to socket_select.
            if ( socket_select($read, $write = NULL, $except = NULL, $tv_sec = 5) < 1 )
            {
                //SocketServer::debug("Problem blocking socket_select?");
                return true;
            }
            
            // Handle new connections.
            if ( in_array($this->master_socket, $read) )
            {
                $this->connected_clients++; // Increment the count.

                for ( $i = 0; $i < $this->connected_clients; $i++ )
                {
                    if ( empty($this->clients[$i]) )
                    {
                        $temp_sock = $this->master_socket;
                        $this->clients[$i] = new SocketServerClient($this->master_socket, $i);
                        $this->trigger_hooks("CONNECT", $this->clients[$i], "");

                        break;
                    }
                }
            }
 
            // Handle input.
            for ( $i = 0; $i < $this->connected_clients; $i++ ) // For each client...
            {
                if ( isset($this->clients[$i]) )
                {
                    if ( in_array($this->clients[$i]->socket, $read) )
                    {
                        if ( !socket_last_error($this->clients[$i]->socket) )
                        {
                            $input = socket_read($this->clients[$i]->socket, $this->max_read, PHP_NORMAL_READ);

                            if ( $input == null )
                            {
                                $this->disconnect($i);
                            }
                            elseif ( strlen($input) > 2 ) // Because, for some reason, the server kept reading newlines from nowhere.
                            {
                                SocketServer::debug("{$i}@{$this->clients[$i]->ip} --> {$input}");
                                $this->trigger_hooks("INPUT", $this->clients[$i], $input);
                            }
                        }
                        else
                        {
                            // Error!
                            echo socket_strerror(socket_last_error());
                        }
                    }
                }
            }

            return true;
        }
 
        /*! @function   disconnect
            @abstract   Disconnects a client from the server.
            @param      int - Index of the client to disconnect.
            @param      string  - Message to send to the hooks
            @result     void
        */
        public function disconnect($client_index, $message = "")
        {
            $i = $client_index;
            SocketServer::debug("Client {$i} from {$this->clients[$i]->ip} Disconnecting");
            $this->trigger_hooks("DISCONNECT", $this->clients[$i], $message);
            $this->clients[$i]->destroy();
            $this->connected_clients--; // Decrement the count.
            unset($this->clients[$i]);
        }
 
        /*! @function   trigger_hooks
            @abstract   Triggers Hooks for a certain command.
            @param      string  - Command who's hooks you want to trigger.
            @param      object  - The client who activated this command.
            @param      string  - The input from the client, or a message to be sent to the hooks.
            @result     void
        */
        public function trigger_hooks($command, &$client,$input)
        {
            if ( isset($this->hooks[$command]) )
            {
                foreach ( $this->hooks[$command] as $function )
                {
                    SocketServer::debug("Triggering Hook '{$function}' for '{$command}'");
                    $continue = call_user_func($function, $this, $client, $input);

                    if ( $continue === FALSE )
                    {
                        break;
                    }
                }
            }
        }
 
        /*! @function   infinite_loop
            @abstract   Runs the server code until the server is shut down.
            @see        loop_once
            @param      void
            @result     void
        */
        public function infinite_loop()
        {
            $test = true;

            do
            {
                $test = $this->loop_once();
            }
            while($test);
        }
 
        /*! @function   debug
            @static
            @abstract   Outputs Text directly.
            @discussion Yeah, should probably make a way to turn this off.
            @param      string  - Text to Output
            @result     void
        */
        public static function debug($text)
        {
            echo("{$text}\r\n");
        }
 
        /*! @function   socket_write_smart
            @static
            @abstract   Writes data to the socket, including the length of the data, and ends it with a CRLF unless specified.
            @discussion It is perfectly valid for socket_write_smart to return zero which means no bytes have been written. Be sure to use the === operator to check for FALSE in case of an error. 
            @param      resource- Socket Instance
            @param      string  - Data to write to the socket.
            @param      string  - Data to end the line with.  Specify a "" if you don't want a line end sent.
            @result     mixed   - Returns the number of bytes successfully written to the socket or FALSE on failure. The error code can be retrieved with socket_last_error(). This code may be passed to socket_strerror() to get a textual explanation of the error.
        */
        public static function socket_write_smart(&$sock, $string, $crlf = "\r\n")
        {
            SocketServer::debug("<-- {$string}");

            if( $crlf )
            {
                $string = "{$string}{$crlf}";
            }

            return socket_write($sock, $string, strlen($string));
        }
 
        /*! @function   __get
            @abstract   Magic method used for allowing the reading of protected variables.
            @discussion You never need to use this method, simply calling $server->variable works because of this method's existence.
            @param      string  - Variable to retrieve
            @result     mixed   - Returns the reference to the variable called.
        */
        function &__get($name)
        {
            return $this->{$name};
        }
    }
 
    /*! @class      SocketServerClient
        @author     Navarr Barnier
        @abstract   A Client Instance for use with SocketServer
     */
    class SocketServerClient
    {
        /*! @var        socket
            @abstract   resource - The client's socket resource, for sending and receiving data with.
         */
        protected $socket;
 
        /*! @var        ip
            @abstract   string - The client's IP address, as seen by the server.
         */
        protected $ip;
 
        /*! @var        hostname
            @abstract   string - The client's hostname, as seen by the server.
            @discussion This variable is only set after calling lookup_hostname, as hostname lookups can take up a decent amount of time.
            @see        lookup_hostname
         */
        protected $hostname;
 
        /*! @var        server_clients_index
            @abstract   int - The index of this client in the SocketServer's client array.
         */
        protected $server_clients_index;

        /*! @var        userID
            @abstract   int - The ID of the user.
         */
        public $userID;

        /*! @var        masked
            @abstract   bool - A flag indicating whether the user would like their last seen timestamp to be hidden.
         */
        public $masked;

        /*! @var        accessToken
            @abstract   string - The token of the user.
         */
        public $accessToken;

        /*! @var        followingList
            @abstract   array - The array of user IDs to whom this user needs to subscribe for receiving event notifications.
         */
        public $followingList;
 
        /*! @function   __construct
            @param      resource- The resource of the socket the client is connecting by, generally the master socket.
            @param      int - The Index in the Server's client array.
            @result     void
         */
        public function __construct(&$socket, $i)
        {
            $this->server_clients_index = $i;
            $this->socket = socket_accept($socket) or die("Failed to Accept");
            SocketServer::debug("New Client Connected");
            socket_getpeername($this->socket, $ip);
            $this->ip = $ip;
            $this->masked = 0;
        }

        /*! @function   getSocket
            @param      void
            @result     socket
         */
        public function getSocket()
        {
            return $this->socket;
        }

        /*! @function   getClientIndex
            @param      void
            @result     int- Returns the index of the client.
         */
        public function getClientIndex()
        {
            return $this->server_clients_index;
        }
        
        /*! @function   lookup_hostname
            @abstract   Searches for the user's hostname and stores the result to hostname.
            @see        hostname
            @param      void
            @result     string  - The hostname on success or the IP address on failure.
         */
        public function lookup_hostname()
        {
            $this->hostname = gethostbyaddr($this->ip);
            return $this->hostname;
        }
 
        /*! @function   destroy
            @abstract   Closes the socket.  Thats pretty much it.
            @param      void
            @result     void
         */
        public function destroy()
        {
            socket_close($this->socket);
        }
 
        function &__get($name)
        {
            return $this->{$name};
        }
        
        function __isset($name)
        {
            return isset($this->{$name});
        }
    }
    
    function establishConnection($server, $client)
    {
        //$output = json_encode(array("messageType" => "connectionStatus", "messageValue" => "authenticationPending"));
        //$server->socket_write_smart($client->getSocket(), $output);
    }
    
    function destroyConnection($server, $client)
    {
        if ( $client->userID )
        {
            $presence;

            if ( $client->masked )
            {
                $presence = "14";
            }
            else
            {
                $presence = "1";
            }

            $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
            $token = $accessTokenTable->getTokenByToken($client->accessToken);
            $accessTokenTable->updateSessionPresence($token["token_id"], $presence);

            // Set the user's presence as offline/masked.
            $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
            $presenceTable->setStatus($client->userID, $presence, $client->masked);

            // Notify all subscribers that this user's gone offline.
            foreach ( $server->clients as $key => $user )
            {
                if ( $user->userID != $client->userID ) // Don't waste time looping over the user who just disconnected.
                {
                    foreach ( $client->followingList as $key => $subscribtion )
                    {
                        foreach ( $server->multipleInstanceClients as $key => $instance )
                        {
                            if ( $instance->userID == $client->userID ) // This client has multiple connected instances. We disconnect them without notifying subscribers.
                            {
                                unset($server->multipleInstanceClients[$key]);

                                return;
                            }
                        }

                        if ( $subscribtion["followed_userid"] == $user->userID )
                        {
                            $output = "while(1);" . json_encode(array("messageType" => "notif_presence", "messageValue" => array("user_id" => $client->userID, "presence" => $presence, "target_id" => "-1"), "errorCode" => "0"));
                            $server->socket_write_smart($user->getSocket(), $output);
                        }
                    }
                }
            }
        }
    }
    
    function handleInput($server, $client, $input)
    {
        /* ====== WITH ENCRYPTION =======
        $input = json_decode($input, true);
        $tokenID = $input['scope'];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);
        $password = $tokenData["token"];

        $decryptor = new RNDecryptor();

        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        //echo "\n\n============\n\nDecrypted payload --> " . $plaintext . "\n\n============\n\n";

        $message = json_decode($plaintext, true);
        $messageType = $message['messageType'];
        $messageValue = $message['messageValue'];*/

        /* ======= NO ENCRYPTION ========
        $input = json_decode($input, true);
        $messageType = $input['messageType'];
        $messageValue = $input['messageValue'];*/

        $input = json_decode($input, true);
        $tokenID = $input['scope'];
        $payload = $input['payload'];

        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $tokenData = $accessTokenTable->getTokenByTokenID($tokenID);
        $password = $tokenData["token"];

        $decryptor = new \RNCryptor\Decryptor();

        // Decryption.
        $plaintext = $decryptor->decrypt($payload, $password); // The decrypted payload.
        SocketServer::debug("\n\n============\n\nDecrypted payload --> " . $plaintext . "\n\n============\n\n");

        $message = json_decode($plaintext, true);
        $messageType = $message['messageType'];
        $messageValue = $message['messageValue'];

        if ( $messageValue['access_token'] )
        {
            $token = $messageValue['access_token'];
            
            $tokenData = $accessTokenTable->getTokenByToken($token);

            if ( $tokenData["user_id"] ) // Does this user exist?
            {
                if ( $messageType == "server_connect" || $messageType == "server_connect_masked" )
                {
                    $userID = $tokenData["user_id"];
                    $presence;

                    if ( $messageType == "connect_masked" )
                    {
                        $presence = "3";
                        $client->masked = 1;
                    }
                    else
                    {
                        $presence = "2";
                    }
                    
                    // Confirm the connection with the client.
                    $output = "while(1);" . json_encode(array("messageType" => $messageType, "messageValue" => array("presence", $presence), "errorCode" => "0"));
                    $server->socket_write_smart($client->getSocket(), $output);

                    $client->userID = $userID;
                    $client->accessToken = $token;

                    $accessTokenTable->updateSessionPresence($tokenID, $presence);

                    // Set the user's presence as online/masked.
                    $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
                    $presenceTable->setStatus($userID, $presence, $client->masked);
                    
                    // Subscribe to event notifications.
                    $followTable = new Scapes_Model_DbTable_Shfollow();
                    $client->followingList = $followTable->getFollowingList($userID);

                    $instanceCounter = 0;

                    // Notify all subscribers of the new user's presence.
                    foreach ( $server->clients as $key => $user )
                    {
                        if ( $user->userID != $userID ) // Don't waste time looping over the user who just connected.
                        {
                            foreach ( $client->followingList as $key => $subscribtion )
                            {
                                if ( $subscribtion["followed_userid"] == $user->userID )
                                {
                                    $output = "while(1);" . json_encode(array("messageType" => "notif_presence", "messageValue" => array("user_id" => $userID, "presence" => $presence), "errorCode" => "0"));
                                    $server->socket_write_smart($user->getSocket(), $output);

                                    break;
                                }
                            }
                        }
                        else
                        {
                            $instanceCounter++; // At minimum, this will be = 1.
                        }
                    }

                    if ( $instanceCounter > 1 ) // Keep track of this client because they're connected thru multiple devices.
                    {
                        $server->multipleInstanceClients[] = $client;
                    }
                }
                elseif ( $messageType == "presence" )
                {
                    $presence = $messageValue['presence'];
                    $targetID = $messageValue['target_id'];
                    $audience = $messageValue['audience'];
                    
                    $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
                    $presenceTable->setStatus($client->userID, $presence, $client->masked, $targetID, $audience);
                    
                    // Notify all subscribers of the user's new presence status.
                    foreach ( $client->followingList as $key => $subscribtion )
                    {
                        foreach ( $server->clients as $key => $user )
                        {
                            if ( $subscribtion["followed_userid"] == $user->userID )
                            {
                                $output = "while(1);" . json_encode(array("messageType" => "notif_presence", "messageValue" => array("user_id" => $client->userID, "presence" => $presence, "target_id" => $targetID, "audience" => $audience), "errorCode" => "0"));
                                $server->socket_write_smart($user->getSocket(), $output);

                                break;
                            }
                        }
                    }
                }
                elseif ( $messageType == "IM_send" )
                {
                    $userTable = new Scapes_Model_DbTable_Shuser();
                    $threadTable = new Scapes_Model_DbTable_Shthread();
                    
                    $threadID = $threadTable->spawnThread($messageValue);
                    $senderID = $messageValue['owner_id'];
                    $senderType = $messageValue['owner_type'];
                    $recipientID = $messageValue['recipient_id'];
                    $message = $messageValue['message'];
                    $audience = $messageValue['audience'];
                    $timestamp_sent = $messageValue['timestamp_sent'];
                    $recipientPresence = getUserPresence($recipientID);
                    $recipientTalkingMask = getUserTalkingMask($recipientID);
                    
                    $userTable->updateMessagesSentCountForUser($senderID);
                    $userTable->updateMessagesReceivedCountForUser($recipientID);
                    
                    $output = "while(1);" . json_encode(array("messageType" => $messageType, "messageValue" => array("thread_id" => $threadID, "owner_id" => $senderID, "owner_type" => $senderType, "recipient_id" => $recipientID, "timestamp_sent" => $timestamp_sent), "errorCode" => "0"));
                    $server->socket_write_smart($client->getSocket(), $output); // Send a confirmation back to the sender.

                    unset($messageValue['access_token']); // Remove the sender's access token before dispatching the message data!
                    
                    $messageValue['thread_id'] = $threadID; // Insert the fresh thread ID.
                    $messageValue['status_sent'] = 1;
                    
                    if ( $recipientPresence["status"] == 1 || $recipientPresence["status"] == 14 || $recipientPresence["status"] == 4 ) // Recipient is offline or away, dispatch a push notification instead.
                    {
                        // Apple push notifications are a maximum of 256 bytes. If they exceed that, the server will reject them.
                        // Trim the alert message while leaving some extra room for the payload.
                        $senderData = $userTable->getUserByUserID($senderID);
                        $firstName = $senderData["name_first"];
                        $lastName = $senderData["name_last"];
                        $recipientName = "{$firstName} {$lastName}";
                        
                        if ( strlen($message) > 160 )
                        {
                            $message = substr($message, 0, 160) . '…';
                        }

                        $applePushProcessor = new Scapes_Model_ApplePush(
                                "{$recipientName}: {$message}",
                                $recipientID,
                                array("type" => "notif_IM", "sender_id" => $senderID, "sender_type" => $senderType),
                                1); // 1 since it's a message that needs replying to.
                        
                        $applePushProcessor->dispatchNotif();
                    }
                    else  // Recipient is online, dispatch the message to them.
                    {
                        foreach ( $server->clients as $key => $user )
                        {
                            if ( $user->userID == $recipientID )
                            {
                                $output = "while(1);" . json_encode(array("messageType" => "notif_IM", "messageValue" => $messageValue, "errorCode" => "0"));
                                $server->socket_write_smart($user->getSocket(), $output);

                                break;
                            }
                        }
                    }

                    // Ad Hoc.
                    if ( $audience == 1 && $recipientTalkingMask == 1 ) // For public threads only, & the recipient must not be masked!
                    {
                        $adHocTable = new Scapes_Model_DbTable_Shadhoc();
                        $listeners = $adHocTable->listenersForConversation($senderID, $recipientID);
                        $messageValue["participants"] = $adHocTable->participantsForConversation($senderID, $recipientID);
                        $messageValue["tag"] = array($senderID, $recipientID);

                        foreach ( $listeners as $mainKey => $listenerID )
                        {
                            $listenerPresence = getUserPresence($listenerID); // Before bothering to loop, check if the listener is even online.

                            if ( $listenerPresence["status"] != 1 && $listenerPresence["status"] != 14 && $listenerPresence["status"] != 4 )
                            {
                                foreach ( $server->clients as $key => $user )
                                {
                                    if ( $user->userID == $listenerID )
                                    {
                                        $output = json_encode(array("messageType" => "notif_ad_hoc", "messageValue" => $messageValue, "errorCode" => "0"));
                                        $server->socket_write_smart($user->getSocket(), encrypt($output, $user->accessToken));
                                        
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                elseif ( $messageType == "IM_delivery" )
                {
                    $threadIDs = json_decode($messageValue['threads']);
                    $senderID = $messageValue['owner_id'];

                    $threadTable = new Scapes_Model_DbTable_Shthread();

                    foreach ( $threadIDs as $mainKey => $threadID )
                    {
                        $threadTable->markDelivered($threadID);
                        $timestamp = $threadTable->getDeliveryDate($threadID);

                        $output = "while(1);" . json_encode(array("messageType" => $messageType, "messageValue" => array("thread_id" => $threadID, "status" => "delivered", "timestamp_delivered" => $timestamp), "errorCode" => "0"));
                        $server->socket_write_smart($client->getSocket(), $output); // Send a confirmation back to the sender of the delivery message.

                        foreach ( $server->clients as $key => $user )
                        {
                            if ( $user->userID == $senderID )
                            {
                               // Notify the sender that the message has been delivered.
                                $output = "while(1);" . json_encode(array("messageType" => "notif_messageStatus", "messageValue" => array("thread_id" => $threadID, "status" => "delivered", "timestamp_delivered" => $timestamp), "errorCode" => "0"));
                                $server->socket_write_smart($user->getSocket(), $output);

                                break;
                            }
                        }
                    }
                }
                elseif ( $messageType == "IM_read" )
                {
                    $threadIDs = json_decode($messageValue['threads']);
                    $senderID = $messageValue['owner_id'];

                    $threadTable = new Scapes_Model_DbTable_Shthread();

                    foreach ( $threadIDs as $mainKey => $threadID )
                    {
                        $threadTable->markRead($threadID);
                        $timestamp = $threadTable->getReadDate($threadID);
                        
                        $output = "while(1);" . json_encode(array("messageType" => $messageType, "messageValue" => array("thread_id" => $threadID, "status" => "read", "timestamp_read" => $timestamp), "errorCode" => "0"));
                        $server->socket_write_smart($client->getSocket(), $output);
                        
                        foreach ( $server->clients as $key => $user )
                        {
                            if ( $user->userID == $senderID )
                            {
                                // Notify the sender that their message has been read.
                                $output = "while(1);" . json_encode(array("messageType" => "notif_messageStatus", "messageValue" => array("thread_id" => $threadID, "status" => "read", "timestamp_read" => $timestamp), "errorCode" => "0"));
                                $server->socket_write_smart($user->getSocket(), $output);

                                break;
                            }
                        }
                    }
                }
                elseif ( $messageType == "IM_delete" )
                {

                }
                elseif ( $messageType == "IM_ad_hoc" )
                {

                }
                elseif ( $messageType == "set_privacy" )
                {
                    $privacy = $messageValue['privacy'];
                    $recipientID = $messageValue['recipient_id'];

                    $threadTable = new Scapes_Model_DbTable_Shthread();
                    $threadTable->changePrivacyBetweenUsers($privacy, $client->userID, $recipientID);

                    $output = "while(1);" . json_encode(array("messageType" => $messageType, "messageValue" => array("privacy" => $privacy, "recipient_id" => $recipientID), "errorCode" => "0"));
                    $server->socket_write_smart($client->getSocket(), $output);

                    foreach ( $server->clients as $key => $user )
                    {
                        if ( $user->userID == $recipientID )
                        {
                            // Notify the recipient that the conversation privacy has changed.
                            $output = "while(1);" . json_encode(array("messageType" => "notif_privacy", "messageValue" => array("privacy" => $privacy, "recipient_id" => $client->userID), "errorCode" => "0"));
                            $server->socket_write_smart($user->getSocket(), $output);
                            
                            break;
                        }
                    }
                }
                elseif ( $messageType == "set_status" )
                {
                    unset($messageValue['access_token']); // Remove the sender's access token before dispatching the status data!

                    // Notify all subscribers of the user's new status update.
                    foreach ( $client->followingList as $key => $subscribtion )
                    {
                        foreach ( $server->clients as $key => $user )
                        {
                            if ( $subscribtion["followed_userid"] == $user->userID )
                            {
                                $output = "while(1);" . json_encode(array("messageType" => "notif_status", "messageValue" => $messageValue, "errorCode" => "0"));
                                $server->socket_write_smart($user->getSocket(), $output);

                                break;
                            }
                        }
                    }
                }
                else
                {
                    // Invalid command.
                    $output = json_encode(array("messageType" => $messageType, "messageValue" => "Invalid command!", "errorCode" => "3"));
                    $server->socket_write_smart($client->getSocket(), encrypt($output, $client->accessToken));
                }
            }
            else
            {
                // Invalid token!
                $output = json_encode(array("messageType" => $messageType, "messageValue" => "", "errorCode" => "2"));
                $server->socket_write_smart($client->getSocket(), encrypt($output, $client->accessToken));

                // Disconnect this client.
                $server->disconnect($client->getClientIndex());
            }
        }
        else
        {
            // No token!
            $output = json_encode(array("messageType" => $messageType, "messageValue" => "", "errorCode" => "1"));
            $server->socket_write_smart($client->getSocket(), encrypt($output, $client->accessToken));

            // Disconnect this client.
            $server->disconnect($client->getClientIndex());
        }
    }

    function encrypt($message, $password)
    {
        $cryptor = new \RNCryptor\Encryptor();
        
        $base64Encrypted = $cryptor->encrypt($message, $password);
        
        return $base64Encrypted;
    }

    function getUserPresence($userID)
    {
        // Use the database for this. Save the server an extra loop over
        // God knows how many clients.
        $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();

        return $presenceTable->getStatus($userID);
    }

    function getUserTalkingMask($userID)
    {
        $userTable = new Scapes_Model_DbTable_Shuser();

        return $userTable->getTalkingMask($userID);
    }

    function resetAllPresence()
    {
        $accessTokenTable = new Scapes_Model_DbTable_Shaccesstoken();
        $accessTokenTable->resetSessionPresenceAll();

        $presenceTable = new Scapes_Model_DbTable_Shuseronlinestatus();
        $presenceTable->resetAll();
    }

    echo "\nLet's start this shit...\n";

    // Reset everyone's presence since they'd be offline when the server is down anyways.
    resetAllPresence();

    // Set the IP and port we will listen on.
    $address = '178.79.166.153';
    $port = 4244;

    $server = new SocketServer($address, $port); // Binds to determined IP.
    $server->hook("connect", "establishConnection"); // On connect does connect_function($server,$client,"");
    $server->hook("disconnect", "destroyConnection"); // On disconnect does disconnect_function($server,$client,"");
    $server->hook("input", "handleInput"); // When receiving input does handle_input($server,$client,$input);
    $server->infinite_loop(); // starts the loop.

?>