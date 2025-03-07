%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(model_apple_token).
-created_by('Ali Mahouk <@MachOSX>').

% application exports
-export([getTokensByUserID/1]).
-export([getTokenBySessionID/1]).
-export([updateBadgeCount/2]).

-include("sh_records.hrl").

%% This function connects to the DB.
init() ->
	crypto:start(),
	application:start(emysql),
	emysql:add_pool(pool_sandbox, 1, "root", "CharlEYbravOsOleio8086", "localhost", 3306, "scapes", utf8).

getTokensByUserID(UserID) ->
	init(),
	emysql:prepare(get_tokens, <<"SELECT * FROM sh_scapes_device_token WHERE user_id = ?">>),
    Results = emysql:execute(pool_sandbox, get_tokens, [UserID]),

    emysql_util:as_record(Results, sh_apple_token, record_info(fields, sh_apple_token)).

getTokenBySessionID(SessionID) ->
	init(),
	emysql:prepare(get_tokens, <<"SELECT * FROM sh_scapes_device_token WHERE session_id = ?">>),
    Results = emysql:execute(pool_sandbox, get_tokens, [SessionID]),

    emysql_util:as_record(Results, sh_apple_token, record_info(fields, sh_apple_token)).

updateBadgeCount(UserID, BadgeCount) ->
	init(),
    emysql:prepare(update_messages_sent_count, <<"UPDATE sh_scapes_device_token SET badge_count = ? WHERE user_id = ?">>),
    emysql:execute(pool_sandbox, update_messages_sent_count, [BadgeCount, UserID]).