<?php

class JwtClass {
    private $senha = "S13KSFJAKDK2J3K5KFDJSKDjdkasjk23jkASDA";
    public function criaToken($payload){
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
         ];
         
         $header = json_encode($header);
         $header = base64_encode($header);
        
         $payload = json_encode($payload);
         $payload = base64_encode($payload);
        
         $signature = hash_hmac('sha256',"$header.$payload",$this->senha,true);
         $signature = base64_encode($signature);
        
         return "$header.$payload.$signature";
        

    }
    public function validaToken($jwt){

      $part = explode(".",$jwt);
      $header = $part[0];
      $payload = $part[1];
      $signature = $part[2];
     
      $signatureCheck = hash_hmac('sha256',"$header.$payload",$this->senha,true);
      $signatureCheck = base64_encode($signatureCheck);
      if ($signature == $signatureCheck){
         $retorno = true;
      }else {
         $retorno = false;
      }
     
      return $retorno;
   }
   public function isExpiredToken($jwt){

      $part = explode(".",$jwt);
      $header = $part[0];
      $payload = $part[1];
      $signature = $part[2];

      // Decodificando JWT Payload de volta para objeto
      $payload = base64_decode($payload);
      $payload = json_decode($payload);
      $time = time();

      if ($payload->exp < $time){
         return false;
      }else {
         return $payload;
      }
   }
   public function extractDataJWT($jwt, $index){

      $part = explode(".",$jwt);
      $extract = $part[$index];
      // Decodificando JWT Payload de volta para objeto
      if ($index < 2) {
         $extract = base64_decode($extract);
         $extract = json_decode($extract);
      }
      
      return $extract;
   }
}
?>