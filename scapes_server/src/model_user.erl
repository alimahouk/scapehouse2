%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(model_user).
-created_by('Ali Mahouk <@MachOSX>').

% application exports
-export([getUserByUserID/1]).
-export([updateMessagesSentCountForUser/1]).
-export([updateMessagesReceivedCountForUser/1]).

-include("sh_records.hrl").

%% This function connects to the DB.
init() ->
	crypto:start(),
	application:start(emysql),
	emysql:add_pool(pool_sandbox, 1, "root", "CharlEYbravOsOleio8086", "localhost", 3306, "scapes", utf8).

getUserByUserID(UserID) ->
	init(),
	emysql:prepare(get_user, <<"SELECT * FROM sh_user WHERE user_id = ?">>),
    Results = emysql:execute(pool_sandbox, get_user, [UserID]),

    emysql_util:as_record(Results, sh_user, record_info(fields, sh_user)).

updateMessagesSentCountForUser(UserID) ->
	init(),
    emysql:prepare(update_messages_sent_count, <<"UPDATE sh_user SET total_messages_sent = total_messages_sent + 1 WHERE user_id = ?">>),
    emysql:execute(pool_sandbox, update_messages_sent_count, [UserID]).

updateMessagesReceivedCountForUser(UserID) ->
	init(),
    emysql:prepare(update_messages_sent_count, <<"UPDATE sh_user SET total_messages_received = total_messages_received + 1 WHERE user_id = ?">>),
    emysql:execute(pool_sandbox, update_messages_sent_count, [UserID]).