%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(model_ad_hoc).
-created_by('Ali Mahouk <@MachOSX>').

% application exports
-export([listenersForConversation/2]).
-export([participantsForConversation/2]).

-include("sh_records.hrl").

%% This function connects to the DB.
init() ->
	crypto:start(),
	application:start(emysql),
	emysql:add_pool(pool_sandbox, 1, "root", "CharlEYbravOsOleio8086", "localhost", 3306, "scapes", utf8).

listenersForConversation(User1, User2) ->
	OriginalUser_1 = min(User1, User2),
	OriginalUser_2 = max(User1, User2),

	init(),
	emysql:prepare(get_listeners, <<"SELECT sh_scapes_follow.follower_userid, sh_scapes_access_token.pid FROM sh_scapes_follow INNER JOIN sh_scapes_access_token ON sh_scapes_access_token.user_id = sh_scapes_follow.follower_userid AND sh_scapes_access_token.session_presence NOT IN (1, 4) AND sh_scapes_follow.followed_userid = ? AND sh_scapes_follow.follower_userid <> ? AND sh_scapes_follow.follower_userid IN (SELECT sh_scapes_follow.follower_userid FROM sh_scapes_follow WHERE sh_scapes_follow.followed_userid = ? AND sh_scapes_follow.follower_userid <> ?)">>),
    Results = emysql:execute(pool_sandbox, get_listeners, [OriginalUser_1, OriginalUser_1, OriginalUser_2, OriginalUser_2]),

    emysql_util:as_record(Results, sh_scapes_listener, record_info(fields, sh_scapes_listener)).

participantsForConversation(User1, User2) ->
	OriginalUser_1 = min(User1, User2),
	OriginalUser_2 = max(User1, User2),

	init(),
	emysql:prepare(get_participants, <<"SELECT user_id FROM sh_scapes_ad_hoc_conversation WHERE original_user_1 = ? AND original_user_2 = ?">>),
    Results = emysql:execute(pool_sandbox, get_participants, [OriginalUser_1, OriginalUser_2]),
    
    emysql_util:as_record(Results, sh_scapes_participant, record_info(fields, sh_scapes_participant)).