%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(chat_liason_sup).
-behavior(supervisor).
-created_by('Ali Mahouk <@MachOSX>').

%% user interface
-export([start_link/0]).

%% gen_server callbacks
-export([init/1]).

start_link() ->
	io:fwrite("liason_sup:start_link()~n"),
	supervisor:start_link({local, chat_liason_sup}, chat_liason_sup, []).

init(Args) ->
	io:fwrite("liason_sup:init(~w)~n", [Args]),
	LiasonSpec = {chat_liason, {chat_liason, start_link, []}
		, temporary, brutal_kill, worker, [chat_liason]},
	StartSpecs = {{simple_one_for_one, 0, 1}, [LiasonSpec]},
	io:fwrite("liason_sup:init() returning~n"),
	{ok, StartSpecs}.
