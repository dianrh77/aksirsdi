<?php

// for side bar menu active
function set_active( $route ) {
    if( is_array( $route ) ){
        return in_array(Request::path(), $route) ? 'active' : '';
    }
    return Request::path() == $route ? 'active' : '';
}

// for side bar menu x-show
function x_show($routes)
{
    if (is_array($routes)) {
        foreach ($routes as $r) {
            if (Request::is($r . '*')) {
                return 'true';
            }
        }
    } else {
        if (Request::is($routes . '*')) {
            return 'true';
        }
    }
    return 'false';
}