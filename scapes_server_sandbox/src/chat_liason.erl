%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(chat_liason).
-behavior(gen_server).
-created_by('Ali Mahouk <@MachOSX>').

%% intermodule exports
-export([string_timestamp/0]).
-export([send_message/2]).% called by dispatcher to deliver a message
-export([start/1]).       % called by acceptor
-export([start_link/1]).  % called by supervisor

%% gen_server callbacks
-export([code_change/3]).
-export([handle_call/3]).
-export([handle_cast/2]).
-export([handle_info/2]).
-export([init/1]).
-export([terminate/2]).

%% internal exports
-export([reader_start/2]).

-include("sh_records.hrl").

%% returns a string representation of the number of seconds since Epoch
string_timestamp() ->
	TS = os:timestamp(),
    {{Year, Month, Day}, {Hour, Minute, Second}} = calendar:now_to_universal_time(TS),

	Timestamp = io_lib:format("~4.10.0B-~2.10.0B-~2.10.0B ~2.10.0B:~2.10.0B:~2.10.0B", [Year, Month, Day, Hour, Minute, Second]),
	list_to_binary(Timestamp).

reader_start(Socket, LiasonPid) ->
	reader_loop(Socket, LiasonPid).

reader_loop(Socket, LiasonPid) ->
	case gen_tcp:recv(Socket, 0) of
		{ok, B} ->
			Content = list_to_binary(lists:subtract(binary_to_list(B), "\r\n")),
			Input = jsx:decode(Content),
			io:format("Parsed the JSON.~n"),
			MessageType = proplists:get_value(<<"messageType">>, Input),
			MessageValue = proplists:get_value(<<"messageValue">>, Input),
			AccessToken = proplists:get_value(<<"access_token">>, MessageValue),
			TokenCheck = model_access_token:getTokenByToken(AccessToken),
	
			case length(TokenCheck) of
				0 -> % Token doesn't exist, kick user.
					gen_tcp:close(Socket);
				_ ->
					%% The result of TokenCheck should technically be only 1 row, so this
					%% loop will only run once.
					[begin
						TokenID = TokenRow#sh_scapes_access_token.token_id,
						UserID = TokenRow#sh_scapes_access_token.user_id,
	
						%% In each case, append the _commx (x is the command number) to all local variables.
						%% This silences those "unsafe variable" errors.
						case iolist_to_binary(MessageType) =:= iolist_to_binary("server_connect") of
							true ->
								io:format("server_connect()~n"),
								Output_server_connect = jsx:encode([{<<"messageType">>, MessageType},{<<"messageValue">>,[{<<"presence">>, 2}]},{<<"errorCode">>, 0}]),
								deliver_message(Socket, Output_server_connect),
	
								io:format("access_token: ~p, for user_id: ~p, mask: OFF~n", [TokenRow#sh_scapes_access_token.token, UserID]),
	
    							model_access_token:logPIDForTokenID(LiasonPid, TokenID),
    							model_access_token:updateSessionPresence(TokenID, 2),
    							model_online_status:setStatus(UserID, 2, -1, 1),
    							
    							RecipientList_server_connect = model_access_token:getRecipientList(UserID),
    							
    							[begin
									PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
									RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_presence">>},{<<"messageValue">>, [{<<"user_id">>, UserID},{<<"presence">>, 2}]},{<<"errorCode">>, 0}]),
									
									send_message(RecipientOutput, PID)
    							end || Recipient <- RecipientList_server_connect];
							false ->
								ok
						end,
						case iolist_to_binary(MessageType) =:= iolist_to_binary("server_connect_masked") of
							true ->
								io:format("server_connect_masked()~n"),
								Output_server_connect_masked = jsx:encode([{<<"messageType">>, MessageType},{<<"messageValue">>,[{<<"presence">>, 3}]},{<<"errorCode">>, 0}]),
								deliver_message(Socket, Output_server_connect_masked),
	
								io:format("access_token: ~p, for user_id: ~p, mask: ON~n", [TokenRow#sh_scapes_access_token.token, UserID]),
	
    							model_access_token:logPIDForTokenID(LiasonPid, TokenID),
    							model_access_token:updateSessionPresence(TokenID, 3),
    							model_online_status:setStatus(UserID, 3, -1, 1),
    							
    							RecipientList_server_connect_masked = model_access_token:getRecipientList(UserID),
    							
    							[begin
									PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
									RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_presence">>},{<<"messageValue">>, [{<<"user_id">>, UserID},{<<"presence">>, 3}]},{<<"errorCode">>, 0}]),
									
									send_message(RecipientOutput, PID)
    							end || Recipient <- RecipientList_server_connect_masked];
							false ->
								ok
						end,
						case iolist_to_binary(MessageType) =:= iolist_to_binary("presence") of
							true ->
								io:format("presence(), user_id: ~p~n", [UserID]),
								Presence_presence = list_to_integer(binary_to_list(proplists:get_value(<<"presence">>, MessageValue))),
								TargetID_presence = proplists:get_value(<<"target_id">>, MessageValue),
								Audience_presence = proplists:get_value(<<"audience">>, MessageValue),
								TalkingMask_presence = list_to_integer(binary_to_list(proplists:get_value(<<"masked">>, MessageValue))),
								
								model_access_token:updateSessionPresence(TokenID, Presence_presence),
    							model_online_status:setStatus(UserID, Presence_presence, TargetID_presence, Audience_presence),
    							
    							case Audience_presence of
									_ when TalkingMask_presence =:= 1, Presence_presence > 4, Presence_presence < 14 -> %% Mask active, notify recipient only.
										RecipientList_presence = model_access_token:getTokensByUserID(TargetID_presence, online),
	
										[begin
											PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
											RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_presence">>},{<<"messageValue">>, [{<<"user_id">>, UserID},{<<"presence">>, Presence_presence},{<<"target_id">>, TargetID_presence},{<<"audience">>, Audience_presence}]},{<<"errorCode">>, 0}]),
											
											send_message(RecipientOutput, PID)
    									end || Recipient <- RecipientList_presence];
									_ ->
										RecipientList_presence = model_access_token:getRecipientList(UserID),
	
										[begin
											PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
											RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_presence">>},{<<"messageValue">>, [{<<"user_id">>, UserID},{<<"presence">>, Presence_presence},{<<"target_id">>, TargetID_presence},{<<"audience">>, Audience_presence}]},{<<"errorCode">>, 0}]),
											
											send_message(RecipientOutput, PID)
    									end || Recipient <- RecipientList_presence]
								end;
							false ->
								ok
						end,
						case iolist_to_binary(MessageType) =:= iolist_to_binary("IM_send") of
							true ->
								io:format("IM_send(), user_id: ~p~n", [UserID]),
								ThreadID_IM_send = model_thread:spawnThread(MessageValue),
								ThreadType_IM_send = list_to_integer(binary_to_list(proplists:get_value(<<"thread_type">>, MessageValue))),
								RecipientID_IM_send = proplists:get_value(<<"recipient_id">>, MessageValue),
    							SenderID_IM_send = proplists:get_value(<<"owner_id">>, MessageValue),
    							SenderType_IM_send = proplists:get_value(<<"owner_type">>, MessageValue),
    							GroupID_IM_send = proplists:get_value(<<"group_id">>, MessageValue),
    							Message_IM_send = proplists:get_value(<<"message">>, MessageValue),
    							Audience_IM_send = proplists:get_value(<<"audience">>, MessageValue),
    							MediaType_IM_send = list_to_integer(binary_to_list(proplists:get_value(<<"media_type">>, MessageValue))),
    							GeneratedMessageID_IM_send = proplists:get_value(<<"thread_id">>, MessageValue),
    							
    							%% Legacy check for older versions that used to send -1 instead of a JSON.
    							%case MediaExtra_IM_send =:= iolist_to_binary("-1") of
    							%	true ->
    							%		MediaExtraType_IM_send = <<"null">>;
    							%	false ->
    							%		MediaExtraType_IM_send = proplists:get_value(<<"attachment_type">>, MediaExtra_IM_send)
    							%end,
								
								Output_IM_send = jsx:encode([{<<"messageType">>, MessageType},{<<"messageValue">>, [{<<"thread_id">>, ThreadID_IM_send},{<<"owner_id">>, SenderID_IM_send},{<<"generated_id">>, GeneratedMessageID_IM_send}]},{<<"errorCode">>, 0}]),
								deliver_message(Socket, Output_IM_send),
								
								model_user:updateMessagesSentCountForUser(SenderID_IM_send),
								model_user:updateMessagesReceivedCountForUser(RecipientID_IM_send),
								
								%% We need to obfuscate location info before sending it to the recipient, unless it's for current location.
								case ThreadType_IM_send of
            						_ when ThreadType_IM_send /= 8 ->
            							%% First delete the old entries.
            							MessageValue_SanitizedLocation_1_IM_send = proplists:delete(<<"location_latitude">>, MessageValue),
            							MessageValue_SanitizedLocation_2_IM_send = proplists:delete(<<"location_longitude">>, MessageValue_SanitizedLocation_1_IM_send),

            							MessageValue_SanitizedLocation_3_IM_send = [{<<"location_latitude">>, <<"">>} | MessageValue_SanitizedLocation_2_IM_send],
										MessageValue_SanitizedLocation_IM_send = [{<<"location_longitude">>, <<"">>} | MessageValue_SanitizedLocation_3_IM_send];
    								_ ->
            							MessageValue_SanitizedLocation_IM_send = MessageValue
    							end,
	
								MessageValueNoToken_IM_send = proplists:delete(<<"access_token">>, MessageValue_SanitizedLocation_IM_send), %% Remove the sender's access token before dispatching the message data!
								MessageValueOutput_IM_send = [{<<"thread_id">>, ThreadID_IM_send} | MessageValueNoToken_IM_send], %% Insert the fresh thread ID.
								MessageValueOutputFinal_IM_send = [{<<"status_sent">>, 1} | MessageValueOutput_IM_send],
								
								RecipientSessions_IM_send = model_access_token:getTokensByUserID(RecipientID_IM_send, all),
								[SenderInfo | _Rest] = model_user:getUserByUserID(SenderID_IM_send),
								
								[begin
									SessionID = Recipient#sh_scapes_access_token.token_id,
									SessionPresence = Recipient#sh_scapes_access_token.session_presence,
	
									case SessionPresence of
										_ when SessionPresence =:= 1; SessionPresence =:= 4; SessionPresence =:= 14 -> %% Recipient is offline or away, dispatch a push notification instead.
											APNSFirstName = SenderInfo#sh_user.name_first,
											APNSLastName = SenderInfo#sh_user.name_last,
											Space = <<" ">>,
											Colon = <<": ">>,

											case string:len(binary_to_list(Message_IM_send)) > 140 of
												true ->
													NameLength = string:len(binary_to_list(APNSFirstName)) + string:len(binary_to_list(APNSLastName)) + 2, %% +2 for the space in between them & the colon after the name.
													APNSMessageBody = string:substr(binary_to_list(Message_IM_send), 1, 140 - NameLength),
													APNSMessageBody_final = list_to_binary(APNSMessageBody ++ "…");
												false ->
													APNSMessageBody_final = Message_IM_send
											end,
											
											case ThreadType_IM_send of
												_ when ThreadType_IM_send =:= 8 ->
													Icon = <<"📍 ">>,
													LocationMessage = <<" sent you a location.">>,
													APNSMessage = <<Icon/binary, APNSFirstName/binary, Space/binary, APNSLastName/binary, LocationMessage/binary>>;
												_ ->
													case MediaType_IM_send of
														1 ->
															Icon = <<"📷 ">>,
															PhotoMessage = <<" sent you a photo.">>,
															APNSMessage = <<Icon/binary, APNSFirstName/binary, Space/binary, APNSLastName/binary, PhotoMessage/binary>>;
														_ ->
															APNSMessage = <<APNSFirstName/binary, Space/binary, APNSLastName/binary, Colon/binary, APNSMessageBody_final/binary>>
													end
											end,
											
											SupData = [{<<"type">>, <<"notif_IM">>},{<<"sender_id">>, SenderID_IM_send},{<<"sender_type">>, SenderType_IM_send},{<<"group_id">>, GroupID_IM_send}],
											chat_apple_push:dispatchNotification(APNSMessage, RecipientID_IM_send, SupData, true, SessionID);
										_ ->
											PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
											RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_IM">>},{<<"messageValue">>, MessageValueOutputFinal_IM_send},{<<"errorCode">>, 0}]),

											send_message(RecipientOutput, PID)
									end
    							end || Recipient <- RecipientSessions_IM_send],
    							
    							case Audience_IM_send of
									_B when Audience_IM_send =:= 1 ->
										Listeners = model_ad_hoc:listenersForConversation(SenderID_IM_send, RecipientID_IM_send),
										
										[begin
											PID = list_to_pid(binary_to_list(Recipient#sh_scapes_listener.pid)),
											Participants = model_ad_hoc:participantsForConversation(SenderID_IM_send, RecipientID_IM_send),
											AdHocMessageValue = [{<<"tag">>, [SenderID_IM_send, RecipientID_IM_send]} | MessageValueOutputFinal_IM_send],
											AdHocMessageValueFinal = [{<<"participants">>, Participants} | AdHocMessageValue],
											AdHocOutput = jsx:encode([{<<"messageType">>, <<"notif_ad_hoc">>},{<<"messageValue">>, AdHocMessageValueFinal},{<<"errorCode">>, 0}]),
											
											send_message(AdHocOutput, PID)
    									end || Recipient <- Listeners];
									_ ->
										ok
								end;
							false ->
								ok
						end,
						case iolist_to_binary(MessageType) =:= iolist_to_binary("IM_delivery") of
							true ->
								io:format("IM_delivery(), user_id: ~p~n", [UserID]),
								ThreadID_IM_delivery = proplists:get_value(<<"thread_id">>, MessageValue),
								SenderID_IM_delivery = proplists:get_value(<<"owner_id">>, MessageValue),
								SenderSessions_IM_delivery = model_access_token:getTokensByUserID(SenderID_IM_delivery, online),
								
								model_thread:markDelivered(ThreadID_IM_delivery),
								TimestampDelivered = string_timestamp(),
								Output_IM_delivery = jsx:encode([{<<"messageType">>, MessageType},{<<"messageValue">>,[{<<"thread_id">>, ThreadID_IM_delivery},{<<"status">>, <<"delivered">>},{<<"timestamp_delivered">>, TimestampDelivered}]},{<<"errorCode">>, 0}]),
								deliver_message(Socket, Output_IM_delivery),
	
								[begin
									SessionPresence = Sender#sh_scapes_access_token.session_presence,
		
									case SessionPresence of
										_ when SessionPresence =:= 1; SessionPresence =:= 4; SessionPresence =:= 14 -> %% Original sender is offline or away.
											ok;
										_ ->
											PID = list_to_pid(binary_to_list(Sender#sh_scapes_access_token.pid)),
											RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_messageStatus">>},{<<"messageValue">>, [{<<"thread_id">>, ThreadID_IM_delivery},{<<"status">>, <<"delivered">>},{<<"timestamp_delivered">>, TimestampDelivered}]},{<<"errorCode">>, 0}]),
											
											send_message(RecipientOutput, PID)
									end
    							end || Sender <- SenderSessions_IM_delivery];
							false ->
								ok
						end,
						case iolist_to_binary(MessageType) =:= iolist_to_binary("IM_read") of
							true ->
								io:format("IM_read(), user_id: ~p~n", [UserID]),
								ThreadID_IM_read = proplists:get_value(<<"thread_id">>, MessageValue),
								SenderID_IM_read = proplists:get_value(<<"owner_id">>, MessageValue),
								SenderSessions_IM_read = model_access_token:getTokensByUserID(SenderID_IM_read, online),
								
								model_thread:markRead(ThreadID_IM_read),
								TimestampRead = string_timestamp(),
								Output_IM_read = jsx:encode([{<<"messageType">>, MessageType},{<<"messageValue">>,[{<<"thread_id">>, ThreadID_IM_read},{<<"status">>, <<"read">>},{<<"timestamp_read">>, TimestampRead}]},{<<"errorCode">>, 0}]),
								deliver_message(Socket, Output_IM_read),
	
								[begin
									SessionPresence = Sender#sh_scapes_access_token.session_presence,
		
									case SessionPresence of
										_ when SessionPresence =:= 1; SessionPresence =:= 4; SessionPresence =:= 14 -> %% Original sender is offline or away.
											ok;
										_ ->
											PID = list_to_pid(binary_to_list(Sender#sh_scapes_access_token.pid)),
											RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_messageStatus">>},{<<"messageValue">>, [{<<"thread_id">>, ThreadID_IM_read},{<<"status">>, <<"read">>},{<<"timestamp_read">>, TimestampRead}]},{<<"errorCode">>, 0}]),
											
											send_message(RecipientOutput, PID)
									end
    							end || Sender <- SenderSessions_IM_read];
							false ->
								ok
						end,
						case iolist_to_binary(MessageType) =:= iolist_to_binary("set_privacy") of
							true ->
								io:format("set_privacy(), user_id: ~p~n", [UserID]),
								Privacy = proplists:get_value(<<"privacy">>, MessageValue),
								RecipientID_set_privacy = proplists:get_value(<<"recipient_id">>, MessageValue),
								model_thread:changePrivacyBetweenUsers(Privacy, UserID, RecipientID_set_privacy),
								
								Output_set_privacy = jsx:encode([{<<"messageType">>, MessageType},{<<"messageValue">>,[{<<"privacy">>, Privacy},{<<"recipient_id">>, RecipientID_set_privacy}]},{<<"errorCode">>, 0}]),
								deliver_message(Socket, Output_set_privacy),
								
								RecipientSessions_set_privacy = model_access_token:getTokensByUserID(RecipientID_set_privacy, online),
	
								%% Notify the recipient that the conversation privacy has changed.
								[begin
									SessionPresence = Recipient#sh_scapes_access_token.session_presence,
									
									case SessionPresence of
										_ when SessionPresence =:= 1; SessionPresence =:= 4; SessionPresence =:= 14 -> %% Original sender is offline or away, dispatch a push notification instead.
											ok;
										_ ->
											PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
											RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_privacy">>},{<<"messageValue">>, [{<<"privacy">>, Privacy},{<<"recipient_id">>, UserID}]},{<<"errorCode">>, 0}]),
											
											send_message(RecipientOutput, PID)
									end
    							end || Recipient <- RecipientSessions_set_privacy];
							false ->
								ok
						end,
						case iolist_to_binary(MessageType) =:= iolist_to_binary("set_status") of
							true ->
								io:format("set_status(), user_id: ~p~n", [UserID]),
								MessageValueNoToken_set_status = proplists:delete(<<"access_token">>, MessageValue), %% Remove the sender's access token before dispatching the message data!
	
								RecipientList_set_status = model_access_token:getRecipientList(UserID),
    							
    							[begin
									PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
									RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_status">>},{<<"messageValue">>, MessageValueNoToken_set_status},{<<"errorCode">>, 0}]),
									
									send_message(RecipientOutput, PID)
    							end || Recipient <- RecipientList_set_status];
							false ->
								ok
						end
    				end || TokenRow <- TokenCheck]
    		end,
	
			reader_loop(Socket, LiasonPid);
		{error, closed} ->
			unlink(LiasonPid),
			gen_server:cast(LiasonPid, socket_closed);
		{error, Reason} ->
			unlink(LiasonPid),
			io:format("Reading from socket failed with error: ~p~n", [Reason]),
			gen_server:cast(LiasonPid, socket_closed)
	end.

deliver_message(Socket, String) ->
	FinalString = ["while(1);" ++ String, list_to_binary("\r\n")],

	case gen_tcp:send(Socket, FinalString) of
		{error, timeout} ->
			io:format("Socket send timeout!~n");
		{error, OtherSendError} ->
            io:format("Some other error on socket (~p), closing...", [OtherSendError]);
        ok ->
            ok
	end.

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% intermodule exports
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

%% called by dispatcher
send_message(String, Pid) ->
	%%%io:fwrite("pid=~w called liason:send_message(\"~s\", ~w)", [self(), String, Pid]),
	gen_server:cast(Pid, {message, String}). % ok

% called by acceptor
start(Socket) ->
	ExtraArgs = [{socket, Socket}],
	supervisor:start_child(chat_liason_sup, ExtraArgs). % simple_one_for_one

%% called by supervisor to start the liason gen_server
start_link(Args) ->
	Options = [],
	gen_server:start_link(chat_liason, Args, Options).

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% gen_server callbacks
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

%% gen_server callbacks
code_change(_OldVsn, State, _Extra) ->
	NewState = State,
	{ok, NewState}.

handle_call(Request, From, State) ->
	{_Socket} = State,
	case Request of
	Other ->
		io:fwrite("unknown call, pid=~w, request=~w~n", [From, Other]),
		{noreply, State}
	end.

handle_cast(Request, State) ->
	{Socket} = State,
	case Request of
	{message, String} ->
		deliver_message(Socket, String),
		{noreply, State};
	socket_closed ->
		io:fwrite("socket closed~n"),
		{stop, normal, State};
	Other ->
		io:fwrite("unknown cast, request=~w~n", [Other]),
		{noreply, State}
	end.

handle_info(Info, State) ->
	{_Socket} = State,
	io:fwrite("got unknown message, info=~w~n", [Info]),
	{noreply, State}.

init(Args) ->
	{socket, Socket} = Args,
	%inet:setopts(Socket, [binary, {packet, line}, {active, false}]),
	io:fwrite("connected, pid=~w~n", [self()]),
	chat_dispatcher:connected(),
	
	spawn_link(chat_liason, reader_start, [Socket, self()]),
	State = {Socket},
	{ok, State}.

%% called when handle_cast returns stop.
%% when a shutdown occurs, all liasons are brutally killed by chat_liason_sup
terminate(Reason, State) ->
	io:format("closing socket...~n"),
	{Socket} = State,
	inet:close(Socket),
	
	%% Set the user as offline & notify subscribers.
	[TokenData | _Rest] = model_access_token:getUserByPID(self()), %% A set is returned. We care about the 1st element.
	
	TokenID = TokenData#sh_scapes_access_token.token_id,
	UserID = TokenData#sh_scapes_access_token.user_id,
	
	PresenceMasks = model_online_status:getPresenceMasks(UserID),
	PresenceMask = PresenceMasks#sh_user_mask.mask_presence,
	
	case PresenceMask =:= 1 of
		true ->
			model_access_token:updateSessionPresence(TokenID, 1),
    		model_online_status:setStatus(UserID, 14, -1, 1);
		false ->
			model_access_token:updateSessionPresence(TokenID, 1),
    		model_online_status:setStatus(UserID, 1, -1, 1)
	end,

	RecipientList = model_access_token:getRecipientList(UserID),
    
    [begin
		PID = list_to_pid(binary_to_list(Recipient#sh_scapes_access_token.pid)),
		
		case PresenceMask =:= 1 of
			true ->
				RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_presence">>},{<<"messageValue">>, [{<<"user_id">>, UserID},{<<"presence">>, 14}]},{<<"errorCode">>, 0}]),
				send_message(RecipientOutput, PID);
			false ->
				RecipientOutput = jsx:encode([{<<"messageType">>, <<"notif_presence">>},{<<"messageValue">>, [{<<"user_id">>, UserID},{<<"presence">>, 1}]},{<<"errorCode">>, 0}]),
				send_message(RecipientOutput, PID)
		end
    end || Recipient <- RecipientList],

	io:fwrite("terminating, pid=~w, reason=~w, mask: ~p~n", [self(), Reason, PresenceMask]),
	chat_dispatcher:disconnected(),
	ok.
