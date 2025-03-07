%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(chat_acceptor).
-created_by('Ali Mahouk <@MachOSX>').

%% intermodule interface
-export([start/0]).

%% internal exports
-export([init/1]).

%% We do not block on send anymore.
-define(TCP_SEND_TIMEOUT, 5000).

port_number() ->
	case application:get_env(port) of
	{ok, Port} ->
		io:fwrite("acceptor: Port specified in app environment: ~w~n", [Port]),
		Port;
	undefined ->
		io:fwrite("acceptor: Port unspecified in app environment. Using default.~n"),
		5222 % default port
	end.

start() ->
	Port = port_number(),
	DefaultOpts = [binary,
					{packet, line},
					{active, false},
					{backlog, 2000},
					{reuseaddr, true},
					{packet_size, 5000},
					{delay_send, false},
					{nodelay, true},
					{send_timeout, ?TCP_SEND_TIMEOUT},
					{keepalive, true},
					{send_timeout_close, true}],
	case gen_tcp:listen(Port, DefaultOpts) of
	{ok, LSock} ->
		io:fwrite("acceptor: listening on port ~B~n", [Port]),
		Pid = spawn_link(chat_acceptor, init, [LSock]),
		{ok, Pid};
	{error, Reason} -> {error, Reason}
	end.

init(LSock) ->
	io:fwrite("acceptor: loop initializing, pid=~w, socket=~w~n"
		, [self(), LSock]),
	loop(LSock).

loop(LSock) ->
	case gen_tcp:accept(LSock) of
	{ok, Socket} ->
		io:fwrite("acceptor: accepted connection, socket=~w~n", [Socket]), 
		chat_liason:start(Socket),
		loop(LSock);
	{error, Reason} ->
		io:fwrite("acceptor: stopping, reason=~w~n", [Reason]),
		exit(Reason)
	end.
