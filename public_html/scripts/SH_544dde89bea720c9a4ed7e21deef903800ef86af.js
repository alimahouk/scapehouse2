
/*
*  Time: Monday, October 01, 2012 at 6:50 pm
*/
function popJelloon(type,content,time){if(!time){time=3;}
var jelloon='<div class="jelloon '+type+'" style="z-index:120;" onclick="$(this).fadeOut();">'+content+'</div>'
$('#mainWrapper').prepend(jelloon);setTimeout(function(){$('.jelloon').fadeOut();},1000*time);}
function is_int(mixed_var){if(typeof mixed_var!=='number'){return false;}
return!(mixed_var%1);}
function nl2br(str,is_xhtml){var breakTag=(is_xhtml||typeof is_xhtml==='undefined')?'<br />':'<br>';return(str+'').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,'$1'+breakTag+'$2');}
function escapeHtml(unsafe){return unsafe.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");}
function replaceURLWithHTMLLinks(text){if(!text){return;}
var replacedText,replacePattern1,replacePattern2;replacePattern1=/(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;replacedText=text.replace(replacePattern1,'<a href="$1" class="externLink">$1</a>');replacePattern2=/(^|[^\/])(www\.[\S]+(\b|$))/gim;replacedText=replacedText.replace(replacePattern2,'$1<a href="http://$2" class="externLink">$2</a>');return replacedText;}
function stripLink(fullLink){   
var replacedText,replacePattern1,replacePattern2;var text=$(fullLink).html();var numofchars=text.length;replacePattern1=/(\b(https?|ftp):\/\/)/gim;replacedText=text.replace(replacePattern1,'');replacePattern2=/(^|[^\/])(www\.)/gim;replacedText=replacedText.replace(replacePattern2,'');  $(fullLink).html(replacedText);if(numofchars>25){$(fullLink).html($(fullLink).html().substring(0,25)+'...');}}
function mentions(text){   
if(!text){return;}else{var replacedText,mention;mention=/(^|\s|[^\w\d])@([\w\-_]+)/gi;replacedText=text.replace(mention,'$1<a href="http://scapehouse.com/$2" class="SHMention"><s>@</s><span>$2</span></a>');return replacedText;}}
function htmlspecialchars_decode(string,quote_style){var optTemp=0,i=0,noquotes=false;if(typeof quote_style==='undefined'){quote_style=2;}
string=string.toString().replace(/&lt;/g,'<').replace(/&gt;/g,'>');var OPTS={'ENT_NOQUOTES':0,'ENT_HTML_QUOTE_SINGLE':1,'ENT_HTML_QUOTE_DOUBLE':2,'ENT_COMPAT':2,'ENT_QUOTES':3,'ENT_IGNORE':4};if(quote_style===0){noquotes=true;}
if(typeof quote_style!=='number'){quote_style=[].concat(quote_style);for(i=0;i<quote_style.length;i++){if(OPTS[quote_style[i]]===0){noquotes=true;}else if(OPTS[quote_style[i]]){optTemp=optTemp|OPTS[quote_style[i]];}}
quote_style=optTemp;}
if(quote_style&OPTS.ENT_HTML_QUOTE_SINGLE){string=string.replace(/&#0*39;/g,"'");}
if(!noquotes){string=string.replace(/&quot;/g,'"');}
string=string.replace(/&amp;/g,'&');return string;}
function cookPost(content){if($('html').hasClass('touch')){return mentions(nl2br(replaceURLWithHTMLLinks(escapeHtml(content))));}else{return textToEmoticon(mentions(nl2br(replaceURLWithHTMLLinks(escapeHtml(content)))));}}
function collapsePublisher(){$("#publisher #editor").removeClass("expanded");$("#publisher #lowerPanel, #publisher .UIPanel").addClass("hidden");}
function parsePicPath(userid,hash,size){if(hash&&hash!="placeholder"){return"/userphotos/"+userid+"/profile/"+size+"_"+hash+".jpg";}else{return"/userphotos/"+size+"_placeholder.png";}}
function autolink(s){var hlink=/\s(ht|f)tp:\/\/([^ \,\;\:\!\)\(\"\'\<\>\f\n\r\t\v])+/g;return(s.replace(hlink,function($0,$1,$2){s=$0.substring(1,$0.length);while(s.length>0&&s.charAt(s.length-1)=='.')
s=s.substring(0,s.length-1);return" "+s.link(s);}));}
function checkTextLen(text,limit){var actualLen;actualLen=strlen(text);if(actualLen>limit){popJelloon('negative','Can\'t post text that is more than '+limit+' characters long! Your text contains '+actualLen+' characters.');return false;}else{return true;}}
function strlen(string){var str=string+'';var i=0,chr='',lgth=0;if(!this.php_js||!this.php_js.ini||!this.php_js.ini['unicode.semantics']||this.php_js.ini['unicode.semantics'].local_value.toLowerCase()!=='on'){return string.length;}
var getWholeChar=function(str,i){var code=str.charCodeAt(i);var next='',prev='';if(0xD800<=code&&code<=0xDBFF){if(str.length<=(i+1)){throw'High surrogate without following low surrogate';}
next=str.charCodeAt(i+1);if(0xDC00>next||next>0xDFFF){throw'High surrogate without following low surrogate';}
return str.charAt(i)+str.charAt(i+1);}else if(0xDC00<=code&&code<=0xDFFF){if(i===0){throw'Low surrogate without preceding high surrogate';}
prev=str.charCodeAt(i-1);if(0xD800>prev||prev>0xDBFF){throw'Low surrogate without preceding high surrogate';}
return false;}
return str.charAt(i);};for(i=0,lgth=0;i<str.length;i++){if((chr=getWholeChar(str,i))===false){continue;}
lgth++;}
return lgth;}
function ISODateString(){var d=new Date();function pad(n){return n<10?'0'+n:n}
return d.getUTCFullYear()+'-'
+pad(d.getUTCMonth()+1)+'-'
+pad(d.getUTCDate())+'T'
+pad(d.getUTCHours())+':'
+pad(d.getUTCMinutes())+':'
+pad(d.getUTCSeconds())+'Z'}
function substr(str,start,len){var i=0,allBMP=true,es=0,el=0,se=0,ret='';str+='';var end=str.length;this.php_js=this.php_js||{};this.php_js.ini=this.php_js.ini||{};switch((this.php_js.ini['unicode.semantics']&&this.php_js.ini['unicode.semantics'].local_value.toLowerCase())){case'on':for(i=0;i<str.length;i++){if(/[\uD800-\uDBFF]/.test(str.charAt(i))&&/[\uDC00-\uDFFF]/.test(str.charAt(i+1))){allBMP=false;break;}}
if(!allBMP){if(start<0){for(i=end-1,es=(start+=end);i>=es;i--){if(/[\uDC00-\uDFFF]/.test(str.charAt(i))&&/[\uD800-\uDBFF]/.test(str.charAt(i-1))){start--;es--;}}}else{var surrogatePairs=/[\uD800-\uDBFF][\uDC00-\uDFFF]/g;while((surrogatePairs.exec(str))!=null){var li=surrogatePairs.lastIndex;if(li-2<start){start++;}else{break;}}}
if(start>=end||start<0){return false;}
if(len<0){for(i=end-1,el=(end+=len);i>=el;i--){if(/[\uDC00-\uDFFF]/.test(str.charAt(i))&&/[\uD800-\uDBFF]/.test(str.charAt(i-1))){end--;el--;}}
if(start>end){return false;}
return str.slice(start,end);}else{se=start+len;for(i=start;i<se;i++){ret+=str.charAt(i);if(/[\uD800-\uDBFF]/.test(str.charAt(i))&&/[\uDC00-\uDFFF]/.test(str.charAt(i+1))){se++;}}
return ret;}
break;}
case'off':default:if(start<0){start+=end;}
end=typeof len==='undefined'?end:(len<0?len+end:len+start);return start>=str.length||start<0||start>end?!1:str.slice(start,end);}
return undefined;}
function strip_tags(input,allowed){allowed=(((allowed||"")+"").toLowerCase().match(/<[a-z][a-z0-9]*>/g)||[]).join('');var tags=/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,commentsAndPhpTags=/<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;return input.replace(commentsAndPhpTags,'').replace(tags,function($0,$1){return allowed.indexOf('<'+$1.toLowerCase()+'>')>-1?$0:'';});}
function clamp(string,allowMore){if(allowMore){rx=/((.+\n+){6})/ig;}else{rx=/((.+\n+){3})/ig;}
var str=string,m=rx.exec(str);cut=rx.lastIndex;returnData=new Object();if(cut){firstLines=m[1].replace(/\s+$/,'');remainder=str.substring(cut);}
else{firstLines=str;remainder='';}
returnData.firstLines=firstLines;returnData.remainder=remainder;return returnData;}
function date(format,timestamp){var that=this,jsdate,f,formatChr=/\\?([a-z])/gi,formatChrCb,_pad=function(n,c){if((n=n+"").length<c){return new Array((++c)-n.length).join("0")+n;}else{return n;}},txt_words=["Sun","Mon","Tues","Wednes","Thurs","Fri","Satur","January","February","March","April","May","June","July","August","September","October","November","December"],txt_ordin={1:"st",2:"nd",3:"rd",21:"st",22:"nd",23:"rd",31:"st"};formatChrCb=function(t,s){return f[t]?f[t]():s;};f={d:function(){return _pad(f.j(),2);},D:function(){return f.l().slice(0,3);},j:function(){return jsdate.getDate();},l:function(){return txt_words[f.w()]+'day';},N:function(){return f.w()||7;},S:function(){return txt_ordin[f.j()]||'th';},w:function(){return jsdate.getDay();},z:function(){var a=new Date(f.Y(),f.n()-1,f.j()),b=new Date(f.Y(),0,1);return Math.round((a-b)/864e5)+1;},W:function(){var a=new Date(f.Y(),f.n()-1,f.j()-f.N()+3),b=new Date(a.getFullYear(),0,4);return 1+Math.round((a-b)/864e5/7);},F:function(){return txt_words[6+f.n()];},m:function(){return _pad(f.n(),2);},M:function(){return f.F().slice(0,3);},n:function(){return jsdate.getMonth()+1;},t:function(){return(new Date(f.Y(),f.n(),0)).getDate();},L:function(){return new Date(f.Y(),1,29).getMonth()===1|0;},o:function(){var n=f.n(),W=f.W(),Y=f.Y();return Y+(n===12&&W<9?-1:n===1&&W>9);},Y:function(){return jsdate.getFullYear();},y:function(){return(f.Y()+"").slice(-2);},a:function(){return jsdate.getHours()>11?"pm":"am";},A:function(){return f.a().toUpperCase();},B:function(){var H=jsdate.getUTCHours()*36e2,i=jsdate.getUTCMinutes()*60,s=jsdate.getUTCSeconds();return _pad(Math.floor((H+i+s+36e2)/86.4)%1e3,3);},g:function(){return f.G()%12||12;},G:function(){return jsdate.getHours();},h:function(){return _pad(f.g(),2);},H:function(){return _pad(f.G(),2);},i:function(){return _pad(jsdate.getMinutes(),2);},s:function(){return _pad(jsdate.getSeconds(),2);},u:function(){return _pad(jsdate.getMilliseconds()*1000,6);},e:function(){throw'Not supported (see source code of date() for timezone on how to add support)';},I:function(){var a=new Date(f.Y(),0),c=Date.UTC(f.Y(),0),b=new Date(f.Y(),6),d=Date.UTC(f.Y(),6);return 0+((a-c)!==(b-d));},O:function(){var a=jsdate.getTimezoneOffset();return(a>0?"-":"+")+_pad(Math.abs(a/60*100),4);},P:function(){var O=f.O();return(O.substr(0,3)+":"+O.substr(3,2));},T:function(){return'UTC';},Z:function(){return-jsdate.getTimezoneOffset()*60;},c:function(){return'Y-m-d\\Th:i:sP'.replace(formatChr,formatChrCb);},r:function(){return'D, d M Y H:i:s O'.replace(formatChr,formatChrCb);},U:function(){return jsdate.getTime()/1000|0;}};this.date=function(format,timestamp){that=this;jsdate=((typeof timestamp==='undefined')?new Date():(timestamp instanceof Date)?new Date(timestamp):new Date(timestamp*1000));return format.replace(formatChr,formatChrCb);};return this.date(format,timestamp);}
function strtotime(str,now){var i,match,s,strTmp='',parse='';strTmp=str;strTmp=strTmp.replace(/\s{2,}|^\s|\s$/g,' ');strTmp=strTmp.replace(/[\t\r\n]/g,'');if(strTmp=='now'){return(new Date()).getTime()/1000;}else if(!isNaN(parse=Date.parse(strTmp))){return(parse/1000);}else if(now){now=new Date(now*1000);}else{now=new Date();}
strTmp=strTmp.toLowerCase();var __is={day:{'sun':0,'mon':1,'tue':2,'wed':3,'thu':4,'fri':5,'sat':6},mon:{'jan':0,'feb':1,'mar':2,'apr':3,'may':4,'jun':5,'jul':6,'aug':7,'sep':8,'oct':9,'nov':10,'dec':11}};var process=function(m){var ago=(m[2]&&m[2]=='ago');var num=(num=m[0]=='last'?-1:1)*(ago?-1:1);switch(m[0]){case'last':case'next':switch(m[1].substring(0,3)){case'yea':now.setFullYear(now.getFullYear()+num);break;case'mon':now.setMonth(now.getMonth()+num);break;case'wee':now.setDate(now.getDate()+(num*7));break;case'day':now.setDate(now.getDate()+num);break;case'hou':now.setHours(now.getHours()+num);break;case'min':now.setMinutes(now.getMinutes()+num);break;case'sec':now.setSeconds(now.getSeconds()+num);break;default:var day;if(typeof(day=__is.day[m[1].substring(0,3)])!='undefined'){var diff=day-now.getDay();if(diff==0){diff=7*num;}else if(diff>0){if(m[0]=='last'){diff-=7;}}else{if(m[0]=='next'){diff+=7;}}
now.setDate(now.getDate()+diff);}}
break;default:if(/\d+/.test(m[0])){num*=parseInt(m[0],10);switch(m[1].substring(0,3)){case'yea':now.setFullYear(now.getFullYear()+num);break;case'mon':now.setMonth(now.getMonth()+num);break;case'wee':now.setDate(now.getDate()+(num*7));break;case'day':now.setDate(now.getDate()+num);break;case'hou':now.setHours(now.getHours()+num);break;case'min':now.setMinutes(now.getMinutes()+num);break;case'sec':now.setSeconds(now.getSeconds()+num);break;}}else{return false;}
break;}
return true;};match=strTmp.match(/^(\d{2,4}-\d{2}-\d{2})(?:\s(\d{1,2}:\d{2}(:\d{2})?)?(?:\.(\d+))?)?$/);if(match!=null){if(!match[2]){match[2]='00:00:00';}else if(!match[3]){match[2]+=':00';}
s=match[1].split(/-/g);for(i in __is.mon){if(__is.mon[i]==s[1]-1){s[1]=i;}}
s[0]=parseInt(s[0],10);s[0]=(s[0]>=0&&s[0]<=69)?'20'+(s[0]<10?'0'+s[0]:s[0]+''):(s[0]>=70&&s[0]<=99)?'19'+s[0]:s[0]+'';return parseInt(this.strtotime(s[2]+' '+s[1]+' '+s[0]+' '+match[2])+(match[4]?match[4]/1000:''),10);}
var regex='([+-]?\\d+\\s'+'(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?'+'|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday'+'|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday)'+'|(last|next)\\s'+'(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?'+'|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday'+'|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday))'+'(\\sago)?';match=strTmp.match(new RegExp(regex,'gi'));if(match==null){return false;}
for(i=0;i<match.length;i++){if(!process(match[i].split(' '))){return false;}}
return(now.getTime()/1000);}