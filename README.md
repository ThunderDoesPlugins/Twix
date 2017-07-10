# Staff Chat
[<img src="https://img.shields.io/badge/Poggit-view-brightgreen.svg" width="100" height="25" />](https://poggit.pmmp.io/ci/ThunderDoesPlugins/Twix/Twix)
[<img src="https://img.shields.io/badge/Discord-join-697EC4.svg" width="100" height="25" />](https://discord.gg/uBghvNp)
<!-- TODO rework Readme.md and SEO tags --!>
<!--
  Title: Twix
  Description: A Twitter plugin with powerful API
  Author: Thunder33345
  -->
<meta name='keywords' content='twitter, pocketmine, plugin, pmmp, mcpe'>
<meta name='description' content='A Twitter plugin with powerful API'>

### Tweet at the comfort of your server!

README MD still need work

## Commands:
Command (args) (shortcut)

/TwixGet \<User> (twg)

/TwixPost \<message> (twp)

/TwixVerify (twv)

## API:
Check out [Prebuilt API](https://github.com/ThunderDoesPlugins/Twix/blob/master/src/Thunder33345/Twix/TwixHelper.php) for common usage 

The main way to use this plugin is to interact with [TwixBuilder](https://github.com/ThunderDoesPlugins/Twix/blob/master/src/Thunder33345/Twix/TwixBuilder.php), tokens URL method are mandatory, most of them are self explanatory

Or

There's a few special ones like setID() and then()

### Then:

DISCLAIMER: This has nothing to do with promises, and DOES NOT function like promises

Then() is a special function invoked AFTER your request(will be done in async) has been executed (this will be done in main thread) (this is not mandatory)

Your function is expected to be ```$function = function(TwixResult $result) {}``` only one and no more

DO NOT PASS callable like [$this,'callback'] since it's done in async PHP wont like it, your function is expected to type hint "TwixResult" which will provide a few useful things for you,

You can see [TwixResult](https://github.com/ThunderDoesPlugins/Twix/blob/master/src/Thunder33345/Twix/Objects/TwixResult.php) will provide server, respond and HTTP respond, if you dont fancy working in functions, you can callback to your plugin but there's no diffrences in doing that

You should also not do ```$function = function(TwixResult $result)use($something) {}``` or anything similars, even tho most are accounted for.. some may not be expected

By default errors will be silenced automatically, and returns null on response/http

### ID:

setId() is basically a handy way for you to pass values around without creating too much mess, this value will be available in then(), useful for identification purpose or just telling whoever send that, that their request succeeded

Please note that DO NOT try to put something non serializable into it(example Server/Player) you should try to re-obtain that instance somehow(ex storing the name and get player by name using the Server provided by TwixResult)

## Error:
This is called when error are thrown in onCompletion task

Your function is expected to be ```$function = function(CompletionError $result) {}```

You can see [CompletionError](https://github.com/ThunderDoesPlugins/Twix/blob/master/src/Thunder33345/Objects/CompletionError.php) for more info

By default errors will be silenced automatically

## Fetch Error:
This is called when error are thrown in onCompletion task

Your function is expected to be ```$function = function(FetchError $result) {}```

By default errors will be silenced automatically, and returns null on response/http

## getResult:

This throws a MissingValueException if a mandatory value is not given, returns TwixFetcher if everything is fulfilled, **please note that YOU are responsible to schedule the async task**