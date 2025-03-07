%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(model_online_status).
-created_by('Ali Mahouk <@MachOSX>').

% application exports
-export([resetAll/0]).
-export([getStatus/1]).
-export([setStatus/4]).
-export([getPresenceMasks/1]).

-include("sh_records.hrl").

%% This function connects to the DB.
init() ->
	crypto:start(),
	application:start(emysql),

	emysql:add_pool(pool_sandbox, 1, "root", "CharlEYbravOsOleio8086", "localhost", 3306, "scapes", utf8).

resetAll() ->
	init(),
  emysql:execute(pool_sandbox, <<"UPDATE sh_user_online_status SET status = 1, target_id = -1, audience = 1, timestamp = DATE_FORMAT(NOW(),'%Y-%m-%d %T') WHERE masked = 0 AND status <> 1">>),
  emysql:execute(pool_sandbox, <<"UPDATE sh_user_online_status SET status = 14, target_id = -1, audience = 1, timestamp = DATE_FORMAT(NOW(),'%Y-%m-%d %T') WHERE masked = 1 AND status <> 14">>).

getStatus(UserID) ->
  init(),
  emysql:prepare(get_status, <<"SELECT * FROM sh_user_online_status WHERE user_id = ?">>),
  Results = emysql:execute(pool_sandbox, get_status, [UserID]),

  emysql_util:as_record(Results, sh_user_online_status, record_info(fields, sh_user_online_status)).

setStatus(UserID, Status, TargetID, Audience) ->
	init(),

	case Status of
		_A when Status =:= 1; Status =:= 4; Status =:= 14 ->
      %% Special cases where we might have multiple connected instances of the same person.
      %% We only set presence to the highest common denominator.
			Tokens = model_access_token:getTokensByUserID(UserID, all),

			try [begin
    		SessionPresence  = TokenRow#sh_scapes_access_token.session_presence,

    			case SessionPresence of
            _ when SessionPresence > 1, SessionPresence < 14 ->
              ActualPresence = SessionPresence,
              emysql:prepare(set_status, <<"UPDATE sh_user_online_status SET status = ?, target_id = ?, audience = ?, timestamp = DATE_FORMAT(NOW(),'%Y-%m-%d %T') WHERE user_id = ?">>),
              emysql:execute(pool_sandbox, set_status, [ActualPresence, TargetID, Audience, UserID]),
              throw(found_one);
    				_ ->
              emysql:prepare(set_status, <<"UPDATE sh_user_online_status SET status = ?, target_id = ?, audience = ?, timestamp = DATE_FORMAT(NOW(),'%Y-%m-%d %T') WHERE user_id = ?">>),
              emysql:execute(pool_sandbox, set_status, [Status, TargetID, Audience, UserID])
    			end
    		end || TokenRow <- Tokens]
    	catch
    		throw:found_one -> ok
    	end;
		_ ->
		  emysql:prepare(set_status, <<"UPDATE sh_user_online_status SET status = ?, target_id = ?, audience = ?, timestamp = DATE_FORMAT(NOW(),'%Y-%m-%d %T') WHERE user_id = ?">>),
   		emysql:execute(pool_sandbox, set_status, [Status, TargetID, Audience, UserID])
	end.

getPresenceMasks(UserID) ->
  init(),
  emysql:prepare(get_presence_mask, <<"SELECT mask_talking, mask_presence FROM sh_user WHERE user_id = ?">>),
  Results = emysql:execute(pool_sandbox, get_presence_mask, [UserID]),

  [RowData | _Rest] = emysql_util:as_record(Results, sh_user_mask, record_info(fields, sh_user_mask)),
  RowData.