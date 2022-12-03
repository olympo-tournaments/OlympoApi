<?php
class Rotas
{
    private $listaRotas = [''];
    private $listaCallback = [''];
    private $listaProtecao = [''];
    private $listaValidacao = [''];

    public function add($metodo, $rota, $callback, $protecao, $validacao)
    {
        $this->listaRotas[] = strtoupper($metodo).':'.$rota;
        $this->listaCallback[] = $callback;
        $this->listaProtecao[] = $protecao;
        $this->listaValidacao[] = $validacao;

        return $this;
    }

    public function ir($rota)
    {
        $param = '';
        $callback = '';
        $protecao = '';
        $validacao = '';
        $methodServer = $_SERVER['REQUEST_METHOD'];
        $methodServer = isset($_POST['_method']) ? $_POST['_method'] : $methodServer;
        $rota = $methodServer.":/".$rota;

        if (substr_count($rota, "/") >= 3) {
            if(substr_count($rota, "/") == 4) {
                $param = substr($rota, strrpos($rota, "/"));
                print_r($param);
                $a = explode("/", $rota);
                $a[3] = "[PARAM]";
                $rota = implode('/', $a);
            }else {
                $param = substr($rota, strrpos($rota, "/")+1);
                $rota = substr($rota, 0, strrpos($rota, "/"))."/[PARAM]";
            }
        }

        
        $indice = array_search($rota, $this->listaRotas);
        if ($indice > 0) {
            $callback = explode("::", $this->listaCallback[$indice]);
            $protecao = $this->listaProtecao[$indice];
            $validacao = $this->listaValidacao[$indice];
        }

        $class = isset($callback[0]) ? $callback[0] : '';
        $method = isset($callback[1]) ? $callback[1] : '';

        $err = new Errors();   
        if (class_exists($class)){     
            if (method_exists($class, $method)){                
                $instanciaClass = new $class();
                if ($protecao) {
                    $verificacaoJwt = new User();
                    $validateJwt = $verificacaoJwt->validateJwt();
                    if ($validateJwt) {
                        if($validacao) {
                            $validateRole = $verificacaoJwt->validateRole($validacao, $validateJwt);
                            if($validateRole) {
                                return call_user_func_array(
                                    array($instanciaClass, $method),
                                    array($param, $validateJwt)
                                );
                            } else {
                                $err = $err->getError("ERR_UNAUTHORIZED");
                                $res = ["errors"=> [$err]];

                                http_response_code($err['status']);
                                echo json_encode($res);
                                exit;
                            }
                        } else {
                            return call_user_func_array(
                                array($instanciaClass, $method),
                                array($param, $validateJwt)
                            );
                        }
                    } else {
                        $err = $err->getError("ERR_TOKEN_EXPIRED");
                        $res = ["errors"=> [$err]];

                        http_response_code($err['status']);
                        echo json_encode($res);
                        exit;
                    }
                } else {
                    return call_user_func_array(
                        array($instanciaClass, $method),
                        array($param)
                    );
                }
            } else {
                $this->naoExiste();
            }
        } else {
            $this->naoExiste();
        }
    }

    private function naoExiste()
    {
        $err = new Errors(); 

        $err = $err->getError("ERR_PAGE_NOT_FOUND");
        $res = ["errors"=> [$err]];

        http_response_code($err['status']);
        echo json_encode($res);
        exit;
    }
}
