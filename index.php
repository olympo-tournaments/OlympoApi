<?php
    include("config.php");

    $roles = ["user"=>0,"vip"=>1, "support"=>2,"moderator"=>3,"adm"=>4,"owner"=>5];

    $rota = new Rotas();

    #user 
    $rota->add('POST', '/api/user', 'User::post', false, $roles['user']);
    $rota->add('GET', '/api/user', 'User::get', true, $roles['adm']);
    $rota->add('GET', '/api/user/[PARAM]', 'User::find', true, $roles['user']);
    $rota->add('DELETE', '/api/user/[PARAM]', 'User::delete', true, $roles['moderator']);
    $rota->add('PATCH', '/api/user/[PARAM]', 'User::patch', true, $roles['user']);
    $rota->add('PUT', '/api/user/[PARAM]', 'User::put', true, $roles['user']);
    $rota->add('GET', '/api/user/stats', 'User::getStats', true, $roles['adm']);

    #auth
    $rota->add('POST', '/api/auth', 'User::authenticate', false, $roles['user']);
    $rota->add('POST', '/api/auth/refresh', 'User::refresh', false, $roles['user']);

    #tournament
    $rota->add('POST', '/api/tournament', 'Tournament::post', true, $roles['user']);
    $rota->add('GET', '/api/tournament', 'Tournament::get', true, $roles['adm']);
    $rota->add('GET', '/api/tournament/[PARAM]', 'Tournament::find', true, $roles['user']);
    $rota->add('PATCH', '/api/user/[PARAM]', 'User::patch', true, $roles['user']);
    $rota->add('DELETE', '/api/tournament/[PARAM]', 'Tournament::delete', true, $roles['moderator']);
    $rota->add('PUT', '/api/tournament/[PARAM]', 'Tournament::put', true, $roles['user']);
    //---
    $rota->add('GET', '/api/tournament/[PARAM]/teams', 'Tournament::getTeams', true, $roles['user']);
    $rota->add('GET', '/api/tournament/[PARAM]/teams', 'Tournament::getStats', true, $roles['user']);
    $rota->add('POST', '/api/tournament/[PARAM]/stats', 'Tournament::setStats', true, $roles['user']);

    #team
    $rota->add('POST', '/api/user', 'Team::post', true, $roles['user']);
    $rota->add('GET', '/api/user', 'Team::get', true, $roles['adm']);
    $rota->add('GET', '/api/user/[PARAM]', 'Team::find', true, $roles['user']);
    $rota->add('DELETE', '/api/user/[PARAM]', 'Team::delete', true, $roles['moderator']);
    $rota->add('PATCH', '/api/user/[PARAM]', 'Team::patch', true, $roles['user']);
    $rota->add('PUT', '/api/user/[PARAM]', 'Team::put', true, $roles['user']);
    $rota->add('GET', '/api/user/[PARAM]/members', 'Team::getMembers', true, $roles['user']);


    if(isset($_GET['url'])) {
        $rota->ir($_GET['url']);
    } else {

        $err = new Errors();

        $err = $err->getError("ERR_INVALID_DATA");
        $res = ["errors"=> [$err]];

        http_response_code($err['status']);
        echo json_encode($res);
    }
?>