<?php

// URL functions -------


function make_clickable ($ret)
{

  //  /\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))/gim
    $ret = ' ' . $ret;
    // in testing, using arrays here was found to be faster
    $ret = preg_replace_callback('/\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/is', '_make_url_clickable_cb', $ret);
    //$ret = preg_replace_callback('#([\s({[>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;!:=,?@\[\]+]+)#is', '_make_web_ftp_clickable_cb', $ret);
    //$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);
    // this one is not in an array because we need it to run last, for cleanup of accidental links within links
    $ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
    $ret = trim($ret);
    return $ret;
}

//function _make_email_clickable_cb ($matches)
//{
//
//    $email = $matches[2] . '@' . $matches[3];
//    return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
//}
//
//function _make_web_ftp_clickable_cb ($matches)
//{
//
//    $ret = '';
//    $dest = $matches[2];
//    $dest = 'http://' . $dest;
//    $dest = esc_url($dest);
//    if (empty($dest))
//        return $matches[0];
//        
//    // removed trailing [.,;:)] from URL
//    if (in_array(substr($dest, - 1), array('.' , ',' , ';' , ':' , ')')) === true) {
//        $ret = substr($dest, - 1);
//        $dest = substr($dest, 0, strlen($dest) - 1);
//    }
//    
//    return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\" target=\"_blank\">$dest</a>$ret";
//}

function _make_url_clickable_cb ($matches)
{
    $url = $matches[0];
    
    $url = esc_url($url);
    
    if (empty($url))
        return $matches[0];
      
    return "<a href=\"$url\" class=\"externLink\" rel=\"nofollow\" target=\"_blank\">$url</a>";
}

function esc_url ($url, $protocols = null)
{

    return clean_url($url, $protocols, 'display');
}

function clean_url ($url, $protocols = null, $context = 'display')
{

    $original_url = $url;
    
    if ('' == $url)
        return $url;
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
    $strip = array('%0d' , '%0a' , '%0D' , '%0A');
    $url = _deep_replace($strip, $url);
    $url = str_replace(';//', '://', $url);
    /* If the URL doesn't appear to contain a scheme, we
               * presume it needs http:// appended (unless a relative
               * link starting with / or a php file).
               */
    if (strpos($url, ':') === false && substr($url, 0, 1) != '/' && substr($url, 0, 1) != '#' && ! preg_match('/^[a-z0-9-]+?\.php/i', $url))
        $url = 'http://' . $url;
        
    // Replace ampersands and single quotes only when displaying.
    if ('display' == $context) {
        $url = preg_replace('/&([^#])(?![a-z]{2,8};)/', '&#038;$1', $url);
        $url = str_replace("'", '&#039;', $url);
    }
    
    if (! is_array($protocols))
        $protocols = array('http' , 'https' , 'ftp' , 'ftps' , 'mailto' , 'news' , 'irc' , 'gopher' , 'nntp' , 'feed' , 'telnet');
    if (wp_kses_bad_protocol($url, $protocols) != $url)
        return '';
    
    return apply_filters('clean_url', $url, $original_url, $context);
}

function _deep_replace ($search, $subject)
{

    $found = true;
    while ($found) {
        $found = false;
        foreach ((array) $search as $val) {
            while (strpos($subject, $val) !== false) {
                $found = true;
                $subject = str_replace($val, '', $subject);
            }
        }
    }
    
    return $subject;
}

function wp_kses_bad_protocol ($string, $allowed_protocols)
{

    $string = wp_kses_no_null($string);
    $string2 = $string . 'a';
    
    while ($string != $string2) {
        $string2 = $string;
        $string = wp_kses_bad_protocol_once($string, $allowed_protocols);
    } # while
    

    return $string;
}

function wp_kses_no_null ($string)
{

    $string = preg_replace('/\0+/', '', $string);
    $string = preg_replace('/(\\\\0)+/', '', $string);
    
    return $string;
}

function wp_kses_bad_protocol_once ($string, $allowed_protocols)
{

    global $_kses_allowed_protocols;
    $_kses_allowed_protocols = $allowed_protocols;
    
    $string2 = preg_split('/:|&#58;|&#x3a;/i', $string, 2);
    if (isset($string2[1]) && ! preg_match('%/\?%', $string2[0]))
        $string = wp_kses_bad_protocol_once2($string2[0]) . trim($string2[1]);
    else
        $string = preg_replace_callback('/^((&[^;]*;|[\sA-Za-z0-9])*)' . '(:|&#58;|&#[Xx]3[Aa];)\s*/', 'wp_kses_bad_protocol_once2', $string);
    
    return $string;
}

function wp_kses_bad_protocol_once2 ($matches)
{

    global $_kses_allowed_protocols;
    
    if (is_array($matches)) {
        if (! isset($matches[1]) || empty($matches[1]))
            return '';
        
        $string = $matches[1];
    } else {
        $string = $matches;
    }
    
    $string2 = wp_kses_decode_entities($string);
    $string2 = preg_replace('/\s/', '', $string2);
    $string2 = wp_kses_no_null($string2);
    $string2 = strtolower($string2);
    
    $allowed = false;
    foreach ((array) $_kses_allowed_protocols as $one_protocol)
        if (strtolower($one_protocol) == $string2) {
            $allowed = true;
            break;
        }
    
    if ($allowed)
        return "$string2:";
    else
        return '';
}

function wp_kses_decode_entities ($string)
{

    $string = preg_replace_callback('/&#([0-9]+);/', '_wp_kses_decode_entities_chr', $string);
    $string = preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', '_wp_kses_decode_entities_chr_hexdec', $string);
    
    return $string;
}

function apply_filters ($tag, $value)
{

    global $wp_filter, $merged_filters, $wp_current_filter;
    
    $args = array();
    $wp_current_filter[] = $tag;
    
    // Do 'all' actions first
    if (isset($wp_filter['all'])) {
        $args = func_get_args();
        _wp_call_all_hook($args);
    }
    
    if (! isset($wp_filter[$tag])) {
        array_pop($wp_current_filter);
        return $value;
    }
    
    // Sort
    if (! isset($merged_filters[$tag])) {
        ksort($wp_filter[$tag]);
        $merged_filters[$tag] = true;
    }
    
    reset($wp_filter[$tag]);
    
    if (empty($args))
        $args = func_get_args();
    
    do {
        foreach ((array) current($wp_filter[$tag]) as $the_)
            if (! is_null($the_['function'])) {
                $args[1] = $value;
                $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
            }
    
    } while (next($wp_filter[$tag]) !== false);
    
    array_pop($wp_current_filter);
    
    return $value;
}

function _wp_call_all_hook ($args)
{

    global $wp_filter;
    
    reset($wp_filter['all']);
    do {
        foreach ((array) current($wp_filter['all']) as $the_)
            if (! is_null($the_['function']))
                call_user_func_array($the_['function'], $args);
    
    } while (next($wp_filter['all']) !== false);
}

function _wp_kses_decode_entities_chr_hexdec ($match)
{

    return chr(hexdec($match[1]));
}

 function _wp_kses_decode_entities_chr( $match ) {
      return chr( $match[1] );
  }

  
