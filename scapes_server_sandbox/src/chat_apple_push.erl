%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(chat_apple_push).
-created_by('Ali Mahouk <@MachOSX>').

%% intermodule exports
-export([dispatchNotification/4, dispatchNotification/5]).

-include("apns.hrl").
-include("sh_records.hrl").
-include("localized.hrl").

-define(APNS_CONNECTION, 'test-apns_connection').

%% Use this function to send a notification to all devices of a certain user.
dispatchNotification(Message, ReceiverID, SupData, SupportsReplying) ->
	ok = apns:start(),

	[AppleTokenData | _] = model_apple_token:getTokensByUserID(ReceiverID, all),
	BadgeCount = model_thread:getTotalUnreadThreadCount(ReceiverID),

	{ok, Pid} = apns:connect(
      	%% your connection identifier:
      	?APNS_CONNECTION,
      	%% called in case of a "hard" error:
      	fun log_error/2,
    	%% called if the device uninstalled the application:
    	fun log_feedback/1
    ),

    Ref = erlang:monitor(process, Pid),

	[begin
		Token = AppleTokenRow#sh_apple_token.token,

		case SupportsReplying of
			true ->
				ok = apns:send_message(apns_connection, #apns_msg{
    				 alert  = jsx:encode([{<<"action-loc-key">>, <<"reply">>},{<<"body">>, Message}]),
    			  	 badge  = BadgeCount,
    			  	 sound  = "beep_1.aif",
    			  	 device_token = Token,
    			  	 extra = SupData
    			});
			false ->
				ok = apns:send_message(apns_connection, #apns_msg{
    				 alert  = Message,
    			  	 badge  = BadgeCount,
    			  	 device_token = Token,
    			  	 extra = SupData
    			})
		end
    end || AppleTokenRow <- AppleTokenData],

    receive
    {'DOWN', Ref, _, _, _} = DownMsg ->
      throw(DownMsg);
    DownMsg ->
      throw(DownMsg)
    after 1000 ->
      ok
    end,

    model_apple_token:updateBadgeCount(ReceiverID, BadgeCount).

%% Use this function for sending a notification to a specific token.
dispatchNotification(Message, ReceiverID, SupData, SupportsReplying, SessionID) ->
	ok = apns:start(),

	AppleTokenRow = model_apple_token:getTokenBySessionID(SessionID),
    
	case length(AppleTokenRow) of
			0 -> % Token doesn't exist.
				ok;
			_ ->
				[AppleTokenData | _] = AppleTokenRow,
				BadgeCount = model_thread:getTotalUnreadThreadCount(ReceiverID),
				
				apns:connect(
    			  	%% your connection identifier:
    			  	apns_connection,
    			  	%% called in case of a "hard" error:
    			  	fun ?MODULE:handle_apns_error/2,
    			  	%% called if the device uninstalled the application:
    			  	fun ?MODULE:handle_apns_delete_subscription/1
    			),
				
				Token = binary_to_list(AppleTokenData#sh_apple_token.token), %% Needs to be a list, not a binary.
                
				case SupportsReplying of
					true ->
						apns:send_message(apns_connection, #apns_msg{
    						alert  = #loc_alert{action = "reply", body = Message},
    					  	badge  = BadgeCount,
    					  	sound  = "beep_1.aif",
    					  	device_token = Token,
    					  	extra = SupData
    					});
                        %Command = io_lib:format("php /opt/nginx/html/trunk/applePush/apns.php \"~ts\" ~p \"~ts\" ~p ~p", [binary_to_list(unicode:characters_to_binary(Message)), BadgeCount, jsx:encode(SupData), Token, 1]),
                        %os:cmd(Command);
					false ->
						apns:send_message(apns_connection, #apns_msg{
    						alert  = Message,
    					  	badge  = BadgeCount,
    					  	device_token = Token,
    					  	extra = SupData
    					})
				end,
			
    			model_apple_token:updateBadgeCount(ReceiverID, BadgeCount)
	end.

log_error(Error, Reason) ->
    io:format("APNS Error ~p: ~p~n", [Error, Reason]),
	ok.

log_feedback(Token) ->
    io:format("Device with token ~p removed the app.~n", [Token]),
	ok.