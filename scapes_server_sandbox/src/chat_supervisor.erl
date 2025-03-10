%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
%%%
%%% chat_supervisor is the top level supervisor for the application.
%%% It is a one_for_one supervisor so it will restart individual processes.
%%% On shutdown, stops processes in reverse order, informs and waits on
%%% supervisors, kills non-supervisors.
%%%
%%% Supervised processes (all permanent):
%%%  * Error Logger (10 seconds)
%%%  * Liason Supervisor
%%%  * Dispatcher (gen_server, brutal_kill)
%%%  * Acceptor Supervisor

-module(chat_supervisor).
-behavior(supervisor).
-created_by('Ali Mahouk <@MachOSX>').

%% user interface
-export([start_link/0]).

%% gen_server callbacks
-export([init/1]).

start_link() ->
	io:fwrite("chat_supervisor:start_link()~n"),
	model_access_token:resetSessionPresenceAll(),
	model_online_status:resetAll(),
	supervisor:start_link({local, chat_supervisor}, chat_supervisor, []).

init(_Args) ->
	io:fwrite("chat_supervisor: initializing, pid=~w~n", [self()]),
	% give error logger 10 seconds to shut down
	LiasonSupervisorSpec = {chat_liason_sup, {chat_liason_sup, start_link, []}
		, permanent, infinity, supervisor, [chat_liason_sup]},
	DispatcherSpec = {chat_dispatcher, {chat_dispatcher, start_link, []}
		, permanent, brutal_kill, worker, [chat_dispatcher]},
	AcceptorSupervisorSpec = {chat_acceptor_sup , {chat_acceptor_sup, start_link, []}
		, permanent, infinity, supervisor, [chat_acceptor_sup]},
	ChildSpecs = [DispatcherSpec, AcceptorSupervisorSpec
		, LiasonSupervisorSpec],
	ok = supervisor:check_childspecs(ChildSpecs),
	StartSpecs = {{one_for_one, 2, 10}, ChildSpecs},
	io:fwrite("chat_supervisor:init() returning~n"),
	{ok, StartSpecs}.
