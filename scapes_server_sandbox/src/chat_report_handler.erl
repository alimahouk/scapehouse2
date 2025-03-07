%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(chat_report_handler).
-behavior(gen_event).
-created_by('Ali Mahouk <@MachOSX>').

%% gen_event callbacks
-export([code_change/3]).
-export([handle_call/2]).
-export([handle_event/2]).
-export([handle_info/2]).
-export([init/1]).
-export([terminate/2]).

%% gen_server callbacks
code_change(_OldVsn, State, _Extra) -> {ok, State}.

handle_call(Request, State) ->
	io:fwrite("report_handler: unknown call, request=~w~n", [Request]),
	{ok, noreply, State}.

handle_event(Event, State) ->
	case Event of
	{error_report, _Gleader, _Data} -> io:fwrite("*** ~w ***~n", [Event]);
	%%%{error, _Gleader, _Data} -> io:fwrite("*** ~w ***", [Event]);
	{info_report, _Gleader, _Data} -> io:fwrite("*** ~w ***~n", [Event]);
	%%%{info_msg, _Gleader, _Data} -> io:fwrite("*** ~w ***", [Event]);
	%%%{info, _Gleader, _Data} -> io:fwrite("*** ~w ***", [Event]);
	_Other -> ok
	end,
	{ok, State}.

handle_info(Info, State) ->
	io:fwrite("report_handler: unknown message, info=~w~n", [Info]),
	{noreply, State}.

init(_Args) ->
	io:fwrite("report_handler: initializing~n"),
	State = {},
	{ok, State}.

terminate(Reason, _State) ->
	io:fwrite("report_handler: terminating, reason=~w~n", [Reason]),
	ok.
