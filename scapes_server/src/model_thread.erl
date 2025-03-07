%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(model_thread).
-created_by('Ali Mahouk <@MachOSX>').

% application exports
-export([spawnThread/1]).
-export([getTotalUnreadThreadCount/1]).
-export([changePrivacyBetweenUsers/3]).
-export([markDelivered/1]).
-export([markRead/1]).
-export([getDeliveryDate/1]).
-export([getReadDate/1]).

-include("emysql.hrl").
-include("sh_records.hrl").

%% This function connects to the DB.
init() ->
	crypto:start(),
	application:start(emysql),
	emysql:add_pool(pool_sandbox, 1, "root", "CharlEYbravOsOleio8086", "localhost", 3306, "scapes", utf8).

spawnThread(MessageData) ->
	init(),
	ThreadType = proplists:get_value(<<"thread_type">>, MessageData),
    RootItemID = proplists:get_value(<<"root_item_id">>, MessageData),
    RecipientID = proplists:get_value(<<"recipient_id">>, MessageData),
    OwnerID = proplists:get_value(<<"owner_id">>, MessageData),
    OwnerType = proplists:get_value(<<"owner_type">>, MessageData),
    GroupID = list_to_integer(binary_to_list(proplists:get_value(<<"group_id">>, MessageData))), %% GroupID needs to be an int!
    Privacy = proplists:get_value(<<"privacy">>, MessageData),
    TimestampSent = proplists:get_value(<<"timestamp_sent">>, MessageData),
    Message = unicode:characters_to_binary(proplists:get_value(<<"message">>, MessageData)),
    LocationLongitude = proplists:get_value(<<"location_longitude">>, MessageData),
    LocationLatitude = proplists:get_value(<<"location_latitude">>, MessageData),
    MediaType = proplists:get_value(<<"media_type">>, MessageData),
    MediaFileSize = proplists:get_value(<<"media_file_size">>, MessageData),
    MediaHash = proplists:get_value(<<"media_hash">>, MessageData),
    MediaExtra = jsx:encode(proplists:get_value(<<"media_extra">>, MessageData)),
	
	InsertID = case GroupID =:= -1 of
		true ->
			emysql:prepare(spawn_thread, <<"INSERT INTO sh_scapes_thread (thread_type, root_item_id, owner_id, owner_type, group_id, privacy, timestamp_sent, message, location_longitude, location_latitude, media_type, media_file_size, media_hash, media_extra) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)">>),
    		Packet = emysql:execute(pool_sandbox, spawn_thread, [ThreadType, RootItemID, OwnerID, OwnerType, Privacy, TimestampSent, Message, LocationLongitude, LocationLatitude, MediaType, MediaFileSize, MediaHash, MediaExtra]),
    		LastInsertID = Packet#ok_packet.insert_id,

    		emysql:prepare(spawn_thread, <<"INSERT INTO sh_scapes_message_dispatch (thread_id, sender_id, sender_type, recipient_id) VALUES (?, ?, ?, ?)">>),
    		emysql:execute(pool_sandbox, spawn_thread, [LastInsertID, OwnerID, OwnerType, RecipientID]),

    		LastInsertID; %% To return this to the main function.
		false ->
			emysql:prepare(spawn_thread, <<"INSERT INTO sh_scapes_thread (thread_type, root_item_id, owner_id, owner_type, group_id, privacy, timestamp_sent, message, location_longitude, location_latitude, media_type, media_file_size, media_hash, media_extra) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)">>),
    		Packet = emysql:execute(pool_sandbox, spawn_thread, [ThreadType, RootItemID, OwnerID, OwnerType, GroupID, Privacy, TimestampSent, Message, LocationLongitude, LocationLatitude, MediaType, MediaFileSize, MediaHash, MediaExtra]),
    		LastInsertID = Packet#ok_packet.insert_id,

    		emysql:prepare(spawn_thread, <<"INSERT INTO sh_scapes_message_dispatch (thread_id, sender_id, sender_type, recipient_id) VALUES (?, ?, ?, ?)">>),
    		emysql:execute(pool_sandbox, spawn_thread, [LastInsertID, OwnerID, OwnerType, RecipientID]),

    		LastInsertID %% To return this to the main function.
	end,

	InsertID. %% return the fresh thread's ID.

%% Gets the number of unread threads (not messages).
getTotalUnreadThreadCount(UserID) ->
	init(),
    emysql:prepare(get_unread_thread_count, <<"SELECT COUNT(*) FROM (SELECT sh_scapes_thread.thread_id, sh_scapes_thread.owner_id FROM sh_scapes_thread INNER JOIN sh_scapes_message_dispatch ON sh_scapes_message_dispatch.thread_id = sh_scapes_thread.thread_id AND sh_scapes_thread.status_read = 0 AND sh_scapes_message_dispatch.recipient_id = ? GROUP BY sh_scapes_thread.owner_id)  AS t">>),
    Results = emysql:execute(pool_sandbox, get_unread_thread_count, [UserID]),
    [CountRow | _]  = Results#result_packet.rows,
    [UnreadCount | _] = CountRow,
    
    %% Return count.
    UnreadCount.

changePrivacyBetweenUsers(Privacy, User1, User2) ->
	init(),
	OriginalUser_1 = min(User1, User2),
	OriginalUser_2 = max(User1, User2),

	case Privacy of
		1 ->
			emysql:prepare(change_conversation_privacy, <<"INSERT INTO sh_scapes_private_conversation (party_1_id, party_2_id, group_id) VALUES (?, ?, NULL)">>),
    		emysql:execute(pool_sandbox, change_conversation_privacy, [OriginalUser_1, OriginalUser_2]);
		_ ->
			emysql:prepare(change_conversation_privacy, <<"DELETE FROM sh_scapes_private_conversation WHERE party_1_id = ? AND party_2_id = ?">>),
    		emysql:execute(pool_sandbox, change_conversation_privacy, [OriginalUser_1, OriginalUser_2])
	end.

markDelivered(ThreadID) ->
	init(),
    emysql:prepare(mark_delivered, <<"UPDATE sh_scapes_thread SET status_delivered = 1, timestamp_delivered = DATE_FORMAT(NOW(),'%Y-%m-%d %T') WHERE thread_id = ?">>),
    emysql:execute(pool_sandbox, mark_delivered, [ThreadID]).

markRead(ThreadID) ->
	init(),
    emysql:prepare(mark_read, <<"UPDATE sh_scapes_thread SET status_delivered = 1, status_read = 1, timestamp_read = DATE_FORMAT(NOW(),'%Y-%m-%d %T') WHERE thread_id = ?">>),
    emysql:execute(pool_sandbox, mark_read, [ThreadID]).

getDeliveryDate(ThreadID) ->
	init(),
	emysql:prepare(get_delivery_date, <<"SELECT timestamp_delivered FROM sh_scapes_thread WHERE thread_id = ?">>),
    Results = emysql:execute(pool_sandbox, get_delivery_date, [ThreadID]),

    TimestampRow = emysql_util:as_record(Results, sh_scapes_thread_timestamp, record_info(fields, sh_scapes_thread_timestamp)),

	Timestamp = case length(TimestampRow) of
		0 -> %% Thread was probably deleted.
			chat_liason:string_timestamp();
		_ ->
			TimestampRow#sh_scapes_thread_timestamp.timestamp_delivered
	end,

	Timestamp.

getReadDate(ThreadID) ->
	init(),
	emysql:prepare(get_read_date, <<"SELECT timestamp_read FROM sh_scapes_thread WHERE thread_id = ?">>),
    Results = emysql:execute(pool_sandbox, get_read_date, [ThreadID]),

    TimestampRow = emysql_util:as_record(Results, sh_scapes_thread_timestamp, record_info(fields, sh_scapes_thread_timestamp)),

	Timestamp = case length(TimestampRow) of
		0 -> %% Thread was probably deleted.
			chat_liason:string_timestamp();
		_ ->
			TimestampRow#sh_scapes_thread_timestamp.timestamp_read
	end,

	Timestamp.