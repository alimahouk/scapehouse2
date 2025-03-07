{application, chat, [
	{description, "Scapes Chat Server"},
	{vsn, "1.0"},
	{modules, [chat, chat_acceptor, chat_acceptor_sup, chat_dispatcher
		, chat_liason, chat_liason_sup, chat_supervisor]},
	{registered, [chat_acceptor_sup, chat_dispatcher, chat_liason_sup
		, chat_supervisor]},
	{applications, [kernel, stdlib]},
	{mod, {chat, []}},
	{env, [{name, "Nightboard Server"}
		, {port, 5222}]}
	]}.
