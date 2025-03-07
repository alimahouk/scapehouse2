%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(chat_acceptor_sup).
-behavior(supervisor_bridge).
-created_by('Ali Mahouk <@MachOSX>').

%% intermodule exports
-export([start_link/0]).

%% supervisor_bridge callbacks
-export([terminate/2]).
-export([init/1]).

start_link() ->
	supervisor_bridge:start_link({local, chat_acceptor_sup}, chat_acceptor_sup, []).

init(_Args) ->
	io:fwrite("acceptor_sup: initializing, pid=~w~n", [self()]),
	{ok, AcceptorPid} = chat_acceptor:start(),
	{ok, AcceptorPid, AcceptorPid}.

terminate(Reason, State) ->
	AcceptorPid = State,
	io:fwrite("acceptor_sup: terminating, reason=~w~n", [Reason]),
	exit(AcceptorPid, Reason).

