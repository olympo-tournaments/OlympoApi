<?php
    include("config.php");

    $roles = ["user"=>0,"vip"=>1, "support"=>2,"moderator"=>3,"adm"=>4,"owner"=>5];

    $rota = new Rotas();

    #user 
    $rota->add('POST', '/api/user', 'User::post', false, $roles['user']); //ok
    $rota->add('GET', '/api/user', 'User::get', true, $roles['adm']); //ok
    $rota->add('GET', '/api/user/[PARAM]', 'User::find', true, $roles['user']); //ok
    $rota->add('DELETE', '/api/user/[PARAM]', 'User::delete', true, $roles['moderator']);
    $rota->add('PATCH', '/api/user/[PARAM]', 'User::patch', true, $roles['user']);
    $rota->add('PUT', '/api/user/[PARAM]', 'User::put', true, $roles['user']);
    $rota->add('GET', '/api/user/stats', 'User::getStats', true, $roles['user']);

    $rota->add('GET', '/user/tournaments', 'User::getUserTournaments', true, $roles['user']); //ok
    $rota->add('GET', '/user/favorites', 'User::getUserFavorites', true, $roles['user']); //ok
    $rota->add('POST', '/user/favorites', 'User::addUserFavorites', true, $roles['user']); //ok

    $rota->add('GET', '/user/allMatches', 'User::getAllUserMatches', true, $roles['user']); //ok
    $rota->add('GET', '/user/matches', 'User::getUserMatches', true, $roles['user']); //ok

    #auth
    $rota->add('POST', '/api/auth', 'User::authenticate', false, $roles['user']); //ok
    $rota->add('POST', '/api/refresh', 'User::refresh', false, $roles['user']); //ok

    #tournament
    $rota->add('POST', '/api/tournament', 'Tournament::post', true, $roles['user']); //ok
    $rota->add('GET', '/api/tournament', 'Tournament::get', true, $roles['adm']); //ok
    $rota->add('GET', '/api/tournament/[PARAM]', 'Tournament::find', true, $roles['user']); //ok
    // $rota->add('PATCH', '/api/user/[PARAM]', 'User::patch', true, $roles['user']);
    // $rota->add('DELETE', '/api/tournament/[PARAM]', 'Tournament::delete', true, $roles['moderator']);
    // $rota->add('PUT', '/api/tournament/[PARAM]', 'Tournament::put', true, $roles['user']);
    // //---
    // $rota->add('GET', '/api/tournament/[PARAM]/teams', 'Tournament::getStats', true, $roles['user']);

    $rota->add('GET', '/tournament/teams/[PARAM]', 'Tournament::getTeams', true, $roles['user']); //ok

    // $rota->add('POST', '/tournament/stats/[PARAM]', 'Tournament::setStats', true, $roles['user']);
    
    $rota->add('GET', '/tournament/categories', 'Tournament::getCategories', true, $roles['user']);//ok
    $rota->add('GET', '/tournament/categories/[PARAM]', 'Tournament::findCategory', true, $roles['user']);//ok
    $rota->add('GET', '/find_tournament/category/[PARAM]', 'Tournament::findTournamentByCategory', true, $roles['user']);//ok
    $rota->add('GET', '/tournament/online', 'Tournament::getOnlineTournaments', true, $roles['user']); //ok
    $rota->add('GET', '/tournament/presencial', 'Tournament::getPresencialTournaments', true, $roles['user']); //ok

    $rota->add('POST', '/tournament/start', 'Tournament::startTournament', true, $roles['user']); //falta determinar as datas!!
    $rota->add('GET', '/tournament/matches/[PARAM]', 'Tournament::getTournamentMatches', true, $roles['user']); //ok
    $rota->add('POST', '/match/finish', 'Tournament::finishMatch', true, $roles['user']); //falta setar as equipes como nao ativas!
    $rota->add('GET', '/api/match/[PARAM]', 'Tournament::findMatch', true, $roles['user']); 
    $rota->add('GET', '/api/matchMembers/[PARAM]', 'Tournament::findMatchMembers', true, $roles['user']); 

    #team`
    $rota->add('POST', '/api/team', 'Team::post', true, $roles['user']); //ok
    $rota->add('GET', '/api/team', 'Team::get', true, $roles['adm']);
    $rota->add('GET', '/api/team/[PARAM]', 'Team::find', true, $roles['user']);
    $rota->add('DELETE', '/api/team/[PARAM]', 'Team::delete', true, $roles['moderator']);
    $rota->add('PATCH', '/api/team/[PARAM]', 'Team::patch', true, $roles['user']);
    $rota->add('PUT', '/api/team/[PARAM]', 'Team::put', true, $roles['user']);

    $rota->add('GET', '/team/members/[PARAM]', 'Team::getMembers', true, $roles['user']);//ok
    $rota->add('POST', '/team/members/[PARAM]', 'Team::addMember', true, $roles['user']);//ok 

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