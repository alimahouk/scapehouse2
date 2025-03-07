%%% Copyright 2014 Scapehouse.
%%% All rights reserved.
-module(chat).
-behaviour(application).
-revision('Revision: 0.7 ').
-created('Date: 28/02/2014 ').
-created_by('Ali Mahouk <@MachOSX>').

% application exports
-export([start/2]).
-export([stop/1]).

start(_Type, _Args) ->
	io:fwrite("Scapes Server starting...~n"),
	application:start(jiffy),
	application:start(xmerl), %% Needed for Apple push notifications!
	chat_supervisor:start_link().

stop(_State) ->
	ok.
