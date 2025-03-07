%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(model_access_token).
-created_by('Ali Mahouk <@MachOSX>').

% application exports
-export([updateSessionPresence/2]).
-export([resetSessionPresenceAll/0]).
-export([getTokenByToken/1]).
-export([getTokenByTokenID/1]).
-export([getTokensByUserID/2]).
-export([getUserByPID/1]).
-export([logPIDForTokenID/2]).
-export([getRecipientList/1]).

-include("sh_records.hrl").

%% This function connects to the DB.
init() ->
	crypto:start(),
	application:start(emysql),
	emysql:add_pool(pool_sandbox, 1, "root", "CharlEYbravOsOleio8086", "localhost", 3306, "scapes", utf8).

updateSessionPresence(TokenID, Presence) ->
	init(),
	
	case Presence =:= 1 of
		true ->
			%% Register a prepared statement.
    		emysql:prepare(update_session_presence, <<"UPDATE sh_scapes_access_token SET session_presence = ?, pid = NULL WHERE token_id = ?">>),
			
    		%% Execute the prepared statement.
    		emysql:execute(pool_sandbox, update_session_presence, [Presence, TokenID]);
		false ->
			emysql:prepare(update_session_presence, <<"UPDATE sh_scapes_access_token SET session_presence = ? WHERE token_id = ?">>),
			emysql:execute(pool_sandbox, update_session_presence, [Presence, TokenID])
	end.
	

resetSessionPresenceAll() ->
	init(),
    emysql:execute(pool_sandbox, <<"UPDATE sh_scapes_access_token SET session_presence = 1, pid = NULL">>).

getTokenByToken(Token) ->
	init(),
    emysql:prepare(select_token, <<"SELECT * FROM sh_scapes_access_token WHERE token = ?">>),
    Results = emysql:execute(pool_sandbox, select_token, [Token]),

    %% Return results.
    emysql_util:as_record(Results, sh_scapes_access_token, record_info(fields, sh_scapes_access_token)).

getTokenByTokenID(TokenID) ->
	init(),
	emysql:prepare(select_token, <<"SELECT * FROM sh_scapes_access_token WHERE token_id = ?">>),
    Results = emysql:execute(pool_sandbox, select_token, [TokenID]),

    emysql_util:as_record(Results, sh_scapes_access_token, record_info(fields, sh_scapes_access_token)).

getTokensByUserID(UserID, PresenceType) ->
	init(),

	Rows = case PresenceType of
		all ->
			%% Sort the results because this is needed when setting a user's presence.
			emysql:prepare(select_token, <<"SELECT * FROM sh_scapes_access_token WHERE user_id = ? ORDER BY session_presence ASC">>),
    		Results = emysql:execute(pool_sandbox, select_token, [UserID]),

    		emysql_util:as_record(Results, sh_scapes_access_token, record_info(fields, sh_scapes_access_token));
		online ->
			emysql:prepare(select_token, <<"SELECT * FROM sh_scapes_access_token WHERE user_id = ? AND session_presence NOT IN (1, 4) ORDER BY session_presence ASC">>),
    		Results = emysql:execute(pool_sandbox, select_token, [UserID]),

    		emysql_util:as_record(Results, sh_scapes_access_token, record_info(fields, sh_scapes_access_token))
	end,

	Rows.

getUserByPID(PID) ->
	init(),
	%% Convert the PID to a string!
	PIDString = io_lib:format("~w", [PID]),
	emysql:prepare(select_pid, <<"SELECT * FROM sh_scapes_access_token WHERE pid = ?">>),
    Results = emysql:execute(pool_sandbox, select_pid, [PIDString]),
    
    emysql_util:as_record(Results, sh_scapes_access_token, record_info(fields, sh_scapes_access_token)).

logPIDForTokenID(PID, TokenID) ->
	init(),
	%% Convert the PID to a string!
	PIDString = io_lib:format("~w", [PID]),
	emysql:prepare(log_pid, <<"UPDATE sh_scapes_access_token SET pid = ? WHERE token_id = ?">>),
    emysql:execute(pool_sandbox, log_pid, [PIDString, TokenID]).

getRecipientList(UserID) ->
	init(),
	emysql:prepare(get_recipient_list, <<"SELECT * FROM sh_scapes_access_token INNER JOIN sh_scapes_follow ON sh_scapes_access_token.user_id = sh_scapes_follow.follower_userid AND sh_scapes_follow.followed_userid = ? AND sh_scapes_follow.follower_userid <> ? AND sh_scapes_access_token.session_presence NOT IN (1, 4) AND sh_scapes_follow.follower_userid NOT IN (SELECT blockee_id FROM sh_blocklist WHERE sh_blocklist.blocker_id = ? AND sh_blocklist.blockee_id = sh_scapes_follow.follower_userid)">>),
    Results = emysql:execute(pool_sandbox, get_recipient_list, [UserID, UserID, UserID]),

    emysql_util:as_record(Results, sh_scapes_access_token, record_info(fields, sh_scapes_access_token)).

